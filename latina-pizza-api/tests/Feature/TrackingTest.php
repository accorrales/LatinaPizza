<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_requires_verified_staff(): void
    {
        $this->getJson('/api/tracking/orders')->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create(['role' => 'cliente']));
        $this->getJson('/api/tracking/orders')->assertForbidden();
        Sanctum::actingAs(User::factory()->unverified()->create(['role' => 'admin']));
        $this->getJson('/api/tracking/orders')->assertStatus(409);
        Sanctum::actingAs(User::factory()->create(['role' => 'cocina', 'sucursal_id' => null]));
        $this->getJson('/api/tracking/orders')->assertForbidden();
    }

    public function test_admin_can_filter_active_and_completed_orders_and_read_history(): void
    {
        $owner = User::factory()->create();
        $active = Pedido::create(['user_id' => $owner->id, 'total' => 5000, 'estado' => 'listo', 'tipo_entrega' => 'express']);
        $active->guardarHistorial('listo');
        Pedido::create(['user_id' => $owner->id, 'total' => 5000, 'estado' => 'entregado', 'tipo_entrega' => 'pickup']);
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson('/api/tracking/orders')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.historial.0.estado', 'listo');
        $this->getJson('/api/tracking/orders?estado=entregado&tipo=pickup')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/tracking/orders?estado=todos')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/tracking/orders?search='.$active->id)->assertJsonPath('data.0.id', $active->id);
        $this->getJson('/api/tracking/orders?page=0')->assertUnprocessable();
        $this->getJson('/api/tracking/orders?estado=en_camino')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_kitchen_cannot_escape_its_branch_with_filters(): void
    {
        $first = Sucursal::create(['nombre' => 'Centro', 'direccion' => 'Centro']);
        $second = Sucursal::create(['nombre' => 'Norte', 'direccion' => 'Norte']);
        $owner = User::factory()->create();
        foreach ([$first, $second] as $branch) {
            Pedido::create(['user_id' => $owner->id, 'sucursal_id' => $branch->id, 'total' => 5000, 'estado' => 'entregado']);
        }
        Sanctum::actingAs(User::factory()->create(['role' => 'cocina', 'sucursal_id' => $first->id]));
        $this->getJson('/api/tracking/orders?estado=todos&sucursal_id='.$second->id)
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.sucursal', 'Centro');
    }

    private function deliveryFixture(): array
    {
        $branch = Sucursal::create(['nombre' => 'Centro', 'direccion' => 'Centro']);
        $owner = User::factory()->create(['role' => 'cliente']);
        $driver = User::factory()->create(['role' => 'delivery', 'sucursal_id' => $branch->id]);
        $order = Pedido::create(['user_id' => $owner->id, 'sucursal_id' => $branch->id, 'estado' => 'listo', 'kitchen_status' => 'listo', 'tipo_entrega' => 'express', 'total' => 5000, 'metodo_pago' => 'efectivo']);

        return [$owner, $driver, $order];
    }

    private function coordinates(): array
    {
        return ['latitude' => 9.93, 'longitude' => -84.08, 'accuracy' => 12, 'recorded_at' => now()->toIso8601String()];
    }

    public function test_tracking_migration_is_reversible_without_losing_orders(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        $migration = require database_path('migrations/2026_09_26_000001_add_live_delivery_tracking_to_pedidos.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('pedidos', 'delivery_latitude'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('pedidos', 'delivery_latitude'));
        $this->assertDatabaseHas('pedidos', ['id' => $order->id, 'estado' => 'listo']);
    }

    public function test_delivery_end_to_end_dispatch_location_owner_and_completion(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->putJson('/api/admin/pedidos/'.$order->id.'/estado', ['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->assertOk();
        $this->assertDatabaseHas('historial_pedidos', ['pedido_id' => $order->id, 'estado' => 'en_camino']);
        $this->patchJson('/api/kitchen/orders/'.$order->id.'/ready')->assertStatus(409);
        $this->patchJson('/api/kitchen/orders/'.$order->id.'/status', ['status' => 'listo'])->assertStatus(409);
        $this->postJson('/api/kitchen/orders/bulk/status', ['ids' => [$order->id], 'status' => 'listo'])->assertJsonPath('affected', 0);
        Sanctum::actingAs($driver);
        $this->getJson('/api/delivery/orders')->assertJsonPath('data.0.id', $order->id);
        $this->postJson('/api/delivery/orders/'.$order->id.'/location', $this->coordinates())->assertOk();
        Sanctum::actingAs($owner);
        $this->getJson('/api/tracking/orders/'.$order->id)->assertOk()->assertJsonPath('data.location.latitude', 9.93);
        $this->getJson('/api/pedidos/'.$order->id)->assertJsonMissingPath('delivery_latitude');
        Sanctum::actingAs($admin);
        $this->putJson('/api/admin/pedidos/'.$order->id.'/estado', ['estado' => 'entregado'])->assertOk();
        $this->getJson('/api/tracking/orders/'.$order->id)->assertJsonPath('data.location', null);
        $this->assertDatabaseHas('pedidos', ['id' => $order->id, 'estado' => 'entregado', 'delivery_latitude' => null, 'payment_status' => 'paid']);
        Sanctum::actingAs($driver);
        $this->postJson('/api/delivery/orders/'.$order->id.'/location', $this->coordinates())->assertStatus(409);
    }

    public function test_only_assigned_verified_driver_can_send_and_only_authorized_users_can_read(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        $order->forceFill(['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->save();
        $otherDriver = User::factory()->create(['role' => 'delivery']);
        Sanctum::actingAs($otherDriver);
        $this->postJson('/api/delivery/orders/'.$order->id.'/location', $this->coordinates())->assertForbidden();
        $this->getJson('/api/delivery/orders')->assertJsonCount(0, 'data');
        $this->getJson('/api/tracking/orders/'.$order->id)->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => 'cliente']));
        $this->getJson('/api/tracking/orders/'.$order->id)->assertForbidden();
        Sanctum::actingAs($owner);
        $this->postJson('/api/delivery/orders/'.$order->id.'/location', $this->coordinates())->assertForbidden();
        $driver->forceFill(['email_verified_at' => null])->save();
        Sanctum::actingAs($driver);
        $this->postJson('/api/delivery/orders/'.$order->id.'/location', $this->coordinates())->assertStatus(409);
    }

    public function test_gps_validation_and_old_positions_cannot_overwrite_new_positions(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        $order->forceFill(['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->save();
        Sanctum::actingAs($driver);
        $endpoint = '/api/delivery/orders/'.$order->id.'/location';
        $this->postJson($endpoint, array_replace($this->coordinates(), ['latitude' => 91, 'longitude' => -181]))->assertUnprocessable();
        $this->postJson($endpoint, array_replace($this->coordinates(), ['recorded_at' => now()->subMinutes(3)->toIso8601String()]))->assertUnprocessable();
        $this->postJson($endpoint, array_replace($this->coordinates(), ['recorded_at' => now()->addMinutes(3)->toIso8601String()]))->assertUnprocessable();
        $captured = now()->utc()->startOfSecond();
        $response = $this->postJson($endpoint, array_replace($this->coordinates(), ['recorded_at' => $captured->toIso8601String()]))->assertOk();
        $this->assertSame($captured->timestamp, \Carbon\Carbon::parse($response->json('data.recorded_at'))->timestamp);
        $this->assertSame($captured->timestamp, $order->fresh()->delivery_recorded_at->timestamp);
        $this->postJson($endpoint, array_replace($this->coordinates(), ['recorded_at' => now()->subSeconds(10)->toIso8601String()]))->assertStatus(409);
    }

    public function test_dispatch_requires_express_ready_and_a_verified_driver_in_the_same_branch(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $endpoint = '/api/admin/pedidos/'.$order->id.'/estado';
        $this->putJson($endpoint, ['estado' => 'en_camino'])->assertUnprocessable();
        $this->putJson($endpoint, ['estado' => 'en_camino', 'delivery_user_id' => $owner->id])->assertUnprocessable();
        $driver->update(['sucursal_id' => null]);
        $this->putJson($endpoint, ['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->assertUnprocessable();
        $driver->update(['sucursal_id' => $order->sucursal_id]);
        $order->update(['estado' => 'preparando']);
        $this->putJson($endpoint, ['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->assertUnprocessable();
        $order->update(['estado' => 'listo', 'tipo_entrega' => 'pickup']);
        $this->putJson($endpoint, ['estado' => 'en_camino', 'delivery_user_id' => $driver->id])->assertUnprocessable();
        $this->putJson($endpoint, ['estado' => 'entregado'])->assertOk();
    }

    public function test_late_payment_webhook_does_not_roll_back_a_dispatched_order(): void
    {
        [$owner, $driver, $order] = $this->deliveryFixture();
        $order->forceFill(['estado' => 'en_camino', 'delivery_user_id' => $driver->id, 'payment_ref' => 'pi_tracking'])->save();
        $secret = 'whsec_tracking_test';
        config(['services.stripe.webhook_secret' => $secret]);
        $payload = json_encode(['id' => 'evt_tracking', 'object' => 'event', 'type' => 'payment_intent.succeeded',
            'data' => ['object' => ['id' => 'pi_tracking', 'object' => 'payment_intent']]]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
        ], $payload)->assertOk();
        $this->assertDatabaseHas('pedidos', ['id' => $order->id, 'estado' => 'en_camino', 'payment_status' => 'paid']);
    }
}
