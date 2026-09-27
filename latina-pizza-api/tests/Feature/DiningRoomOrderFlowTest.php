<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\MesaSesion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiningRoomOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_table_flow_uses_immutable_rounds_and_requires_service_and_payment_before_close(): void
    {
        [$branch, $waiter, $cashier, $table, $session, $product] = $this->diningRoomFixture();

        Sanctum::actingAs($waiter);

        $roundOne = $this->postJson("/api/salon/sesiones/{$session->id}/rondas", [
            'items' => [[
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'cantidad' => 2,
                'extras' => [],
                'nota_cliente' => 'Sin hielo',
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.ronda', 1)
            ->assertJsonPath('data.kitchen_status', 'nuevo')
            ->assertJsonPath('data.payment_status', 'pending')
            ->assertJsonPath('data.total', 3000);

        $firstOrderId = $roundOne->json('data.id');

        $roundTwo = $this->postJson("/api/salon/sesiones/{$session->id}/rondas", [
            'items' => [[
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'cantidad' => 1,
                'extras' => [],
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.ronda', 2)
            ->assertJsonPath('data.total', 1500);

        $secondOrderId = $roundTwo->json('data.id');

        $this->assertDatabaseHas('pedidos', [
            'id' => $firstOrderId,
            'mesa_sesion_id' => $session->id,
            'created_by_user_id' => $waiter->id,
            'canal_venta' => Pedido::CANAL_SALON,
            'tipo_entrega' => 'salon',
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $firstOrderId,
            'producto_id' => $product->id,
            'cantidad' => 2,
            'precio_total' => 3000,
        ]);

        $this->postJson("/api/salon/sesiones/{$session->id}/cerrar")
            ->assertConflict()
            ->assertJsonPath('message', 'La mesa todavía tiene rondas pendientes de servir.');

        Pedido::whereKey($firstOrderId)->update(['kitchen_status' => 'listo', 'estado' => 'listo']);
        Pedido::whereKey($secondOrderId)->update(['kitchen_status' => 'listo', 'estado' => 'listo']);

        $this->postJson("/api/salon/pedidos/{$firstOrderId}/servir")
            ->assertOk()
            ->assertJsonPath('data.kitchen_status', 'entregado');
        $this->postJson("/api/salon/pedidos/{$secondOrderId}/servir")
            ->assertOk()
            ->assertJsonPath('data.kitchen_status', 'entregado');

        $this->postJson("/api/salon/sesiones/{$session->id}/cerrar")
            ->assertConflict()
            ->assertJsonPath('message', 'La cuenta todavía tiene rondas pendientes de pago.');

        Sanctum::actingAs($cashier);
        $this->postJson("/api/salon/sesiones/{$session->id}/pagar", [
            'metodo_pago' => 'efectivo',
        ])->assertOk()
            ->assertJsonPath('data.paid_total', 4500)
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.ready_to_close', true);

        $this->assertDatabaseHas('pedidos', ['id' => $firstOrderId, 'payment_status' => 'paid']);
        $this->assertDatabaseHas('pedidos', ['id' => $secondOrderId, 'payment_status' => 'paid']);

        Sanctum::actingAs($waiter);
        $this->postJson("/api/salon/sesiones/{$session->id}/cerrar")
            ->assertOk()
            ->assertJsonPath('data.estado', Mesa::DISPONIBLE);

        $this->assertDatabaseHas('mesa_sesiones', [
            'id' => $session->id,
            'estado' => MesaSesion::CERRADA,
        ]);
        $this->assertDatabaseHas('mesas', [
            'id' => $table->id,
            'estado' => Mesa::DISPONIBLE,
        ]);
    }

    public function test_waiter_cannot_cancel_round_and_manager_can_only_cancel_before_kitchen_starts(): void
    {
        [$branch, $waiter, $cashier, $table, $session, $product] = $this->diningRoomFixture();
        $manager = User::factory()->create([
            'role' => User::ROLE_GERENTE,
            'sucursal_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($waiter);
        $response = $this->postJson("/api/salon/sesiones/{$session->id}/rondas", [
            'items' => [[
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'cantidad' => 1,
                'extras' => [],
            ]],
        ])->assertCreated();
        $orderId = $response->json('data.id');

        $this->postJson("/api/salon/pedidos/{$orderId}/cancelar")
            ->assertForbidden();

        Sanctum::actingAs($manager);
        $this->postJson("/api/salon/pedidos/{$orderId}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelado');

        Sanctum::actingAs($waiter);
        $second = $this->postJson("/api/salon/sesiones/{$session->id}/rondas", [
            'items' => [[
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'cantidad' => 1,
                'extras' => [],
            ]],
        ])->assertCreated();
        $secondOrderId = $second->json('data.id');
        Pedido::whereKey($secondOrderId)->update(['kitchen_status' => 'preparacion', 'estado' => 'preparando']);

        Sanctum::actingAs($manager);
        $this->postJson("/api/salon/pedidos/{$secondOrderId}/cancelar")
            ->assertConflict();

        $this->assertDatabaseHas('pedidos', [
            'id' => $secondOrderId,
            'kitchen_status' => 'preparacion',
        ]);
    }

    public function test_cashier_can_view_and_pay_own_branch_but_cannot_create_rounds(): void
    {
        [$branch, $waiter, $cashier, $table, $session, $product] = $this->diningRoomFixture();

        Sanctum::actingAs($cashier);

        $this->getJson("/api/salon/sesiones/{$session->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $session->id);

        $this->postJson("/api/salon/sesiones/{$session->id}/rondas", [
            'items' => [[
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'cantidad' => 1,
            ]],
        ])->assertForbidden();
    }

    private function diningRoomFixture(): array
    {
        $branch = Sucursal::create(['nombre' => 'Naranjo', 'direccion' => 'Centro']);
        $waiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $cashier = User::factory()->create([
            'role' => User::ROLE_CAJERO,
            'sucursal_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $table = Mesa::create([
            'sucursal_id' => $branch->id,
            'numero' => '20',
            'capacidad' => 4,
            'estado' => Mesa::OCUPADA,
        ]);
        $session = MesaSesion::create([
            'mesa_id' => $table->id,
            'sucursal_id' => $branch->id,
            'mesero_user_id' => $waiter->id,
            'estado' => MesaSesion::ABIERTA,
            'personas' => 2,
        ]);
        $category = Categoria::create(['nombre' => 'Bebidas', 'descripcion' => 'Bebidas de salón']);
        $product = Producto::create([
            'nombre' => 'Refresco lata',
            'precio' => 1500,
            'estado' => true,
            'categoria_id' => $category->id,
        ]);

        return [$branch, $waiter, $cashier, $table, $session, $product];
    }
}
