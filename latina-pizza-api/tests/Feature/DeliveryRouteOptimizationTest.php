<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryRouteOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private function activeOrder(User $owner, User $driver, Sucursal $branch, float $latitude, float $longitude, string $name): Pedido
    {
        $order = Pedido::create([
            'user_id' => $owner->id,
            'sucursal_id' => $branch->id,
            'estado' => Pedido::EN_CAMINO,
            'kitchen_status' => 'listo',
            'tipo_entrega' => 'express',
            'total' => 5000,
            'delivery_address_json' => [
                'nombre' => $name,
                'direccion_exacta' => $name,
                'latitud' => $latitude,
                'longitud' => $longitude,
            ],
        ]);
        $order->forceFill(['delivery_user_id' => $driver->id])->save();

        return $order;
    }

    public function test_one_gps_signal_updates_every_active_order_for_the_driver(): void
    {
        $branch = Sucursal::create(['nombre' => 'Centro', 'direccion' => 'Centro']);
        $driver = User::factory()->create(['role' => 'delivery', 'sucursal_id' => $branch->id]);
        $first = $this->activeOrder(User::factory()->create(['role' => 'cliente']), $driver, $branch, 9.94, -84.09, 'Casa A');
        $second = $this->activeOrder(User::factory()->create(['role' => 'cliente']), $driver, $branch, 9.95, -84.10, 'Casa B');

        Sanctum::actingAs($driver);
        $response = $this->postJson('/api/delivery/location', [
            'latitude' => 9.93,
            'longitude' => -84.08,
            'accuracy' => 12,
            'recorded_at' => now()->toIso8601String(),
        ])->assertOk();

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $response->json('data.order_ids'));
        foreach ([$first, $second] as $order) {
            $order->refresh();
            $this->assertSame(9.93, $order->delivery_latitude);
            $this->assertSame(-84.08, $order->delivery_longitude);
        }
    }

    public function test_route_is_optimized_by_travel_time_and_customer_only_sees_their_stop(): void
    {
        $branch = Sucursal::create([
            'nombre' => 'Centro',
            'direccion' => 'Centro',
            'latitud' => 9.9300,
            'longitud' => -84.0800,
        ]);
        $driver = User::factory()->create(['role' => 'delivery', 'sucursal_id' => $branch->id]);
        $ownerA = User::factory()->create(['role' => 'cliente']);
        $ownerB = User::factory()->create(['role' => 'cliente']);
        $orderA = $this->activeOrder($ownerA, $driver, $branch, 9.9500, -84.1000, 'Casa A');
        $orderB = $this->activeOrder($ownerB, $driver, $branch, 9.9400, -84.0900, 'Casa B');

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/table/')) {
                return Http::response([
                    'code' => 'Ok',
                    'durations' => [[0, 600, 300], [600, 0, 200], [300, 200, 0]],
                    'distances' => [[0, 6000, 3000], [6000, 0, 2000], [3000, 2000, 0]],
                ]);
            }

            return Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'duration' => 500,
                    'distance' => 5000,
                    'geometry' => ['type' => 'LineString', 'coordinates' => [[-84.08, 9.93], [-84.09, 9.94], [-84.10, 9.95]]],
                    'legs' => [['duration' => 300, 'distance' => 3000], ['duration' => 200, 'distance' => 2000]],
                ]],
            ]);
        });

        Sanctum::actingAs($driver);
        $this->getJson('/api/delivery/route')->assertOk()
            ->assertJsonPath('data.stops.0.order_id', $orderB->id)
            ->assertJsonPath('data.stops.1.order_id', $orderA->id)
            ->assertJsonPath('data.stops.0.sequence', 1)
            ->assertJsonPath('data.total_seconds', 980);

        Sanctum::actingAs($ownerA);
        $this->getJson('/api/tracking/orders/'.$orderA->id)->assertOk()
            ->assertJsonPath('data.route.stops_before', 1)
            ->assertJsonPath('data.route.destination.latitude', 9.95)
            ->assertJsonMissingPath('data.route.stops')
            ->assertJsonMissingPath('data.route.geometry');

        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson('/api/tracking/orders/'.$orderA->id)->assertOk()
            ->assertJsonPath('data.route.stops.0.order_id', $orderB->id)
            ->assertJsonPath('data.route.stops.1.order_id', $orderA->id);
    }

    public function test_route_falls_back_without_exposing_other_customer_addresses(): void
    {
        $branch = Sucursal::create(['nombre' => 'Centro', 'direccion' => 'Centro', 'latitud' => 9.93, 'longitud' => -84.08]);
        $driver = User::factory()->create(['role' => 'delivery', 'sucursal_id' => $branch->id]);
        $owner = User::factory()->create(['role' => 'cliente']);
        $order = $this->activeOrder($owner, $driver, $branch, 9.94, -84.09, 'Casa privada');

        Http::fake(fn () => Http::response(['code' => 'Error'], 503));

        Sanctum::actingAs($driver);
        $this->getJson('/api/delivery/route')->assertOk()
            ->assertJsonPath('data.provider', 'fallback')
            ->assertJsonPath('data.approximate', true)
            ->assertJsonPath('data.stops.0.order_id', $order->id);

        Sanctum::actingAs($owner);
        $this->getJson('/api/tracking/orders/'.$order->id)->assertOk()
            ->assertJsonPath('data.route.destination.latitude', 9.94)
            ->assertJsonMissingPath('data.route.stops.0.address');
    }
}
