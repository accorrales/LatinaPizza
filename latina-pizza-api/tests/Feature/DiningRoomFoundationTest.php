<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\MesaSesion;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiningRoomFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_dining_room_order_keeps_table_session_and_employee_traceability(): void
    {
        $branch = Sucursal::create([
            'nombre' => 'Naranjo',
            'direccion' => 'Centro',
        ]);
        $waiter = User::factory()->create([
            'role' => User::ROLE_MESERO,
            'sucursal_id' => $branch->id,
        ]);
        $table = Mesa::create([
            'sucursal_id' => $branch->id,
            'numero' => '8',
            'zona' => 'Salon principal',
            'capacidad' => 4,
        ]);
        $session = MesaSesion::create([
            'mesa_id' => $table->id,
            'sucursal_id' => $branch->id,
            'mesero_user_id' => $waiter->id,
            'personas' => 4,
        ]);

        $order = Pedido::create([
            'user_id' => null,
            'sucursal_id' => $branch->id,
            'total' => 18500,
            'estado' => 'pendiente',
            'tipo_entrega' => 'salon',
            'tipo_pedido' => 'salon',
            'canal_venta' => Pedido::CANAL_SALON,
            'mesa_sesion_id' => $session->id,
            'created_by_user_id' => $waiter->id,
        ]);

        $this->assertNull($order->user_id);
        $this->assertSame(Pedido::CANAL_SALON, $order->canal_venta);
        $this->assertTrue($order->mesaSesion->is($session));
        $this->assertTrue($order->createdBy->is($waiter));
        $this->assertTrue($session->pedidos->contains($order));
        $this->assertTrue($table->sesionActiva->is($session));
    }

    public function test_existing_order_defaults_to_web_sales_channel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CLIENTE]);

        $order = Pedido::create([
            'user_id' => $customer->id,
            'total' => 5000,
            'estado' => 'pendiente',
            'tipo_entrega' => 'pickup',
        ]);

        $this->assertSame(Pedido::CANAL_WEB, $order->fresh()->canal_venta);
    }

    public function test_admin_can_assign_waiter_cashier_and_manager_roles_only_with_branch(): void
    {
        $branch = Sucursal::create([
            'nombre' => 'Naranjo',
            'direccion' => 'Centro',
        ]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employee = User::factory()->create(['role' => User::ROLE_CLIENTE]);
        Sanctum::actingAs($admin);

        foreach ([User::ROLE_MESERO, User::ROLE_CAJERO, User::ROLE_GERENTE] as $role) {
            $payload = [
                'name' => $employee->name,
                'email' => $employee->email,
                'role' => $role,
            ];

            $this->putJson('/api/admin/usuarios/'.$employee->id, $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('sucursal_id');

            $this->putJson('/api/admin/usuarios/'.$employee->id, $payload + ['sucursal_id' => $branch->id])
                ->assertOk();

            $this->assertDatabaseHas('users', [
                'id' => $employee->id,
                'role' => $role,
                'sucursal_id' => $branch->id,
            ]);
            $employee->refresh();
        }
    }

    public function test_changing_staff_email_requires_reverification_and_revokes_tokens(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employee = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'email_verified_at' => now(),
        ]);
        $employee->createToken('existing-session');
        $this->assertSame(1, $employee->tokens()->count());

        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/usuarios/'.$employee->id, [
            'name' => $employee->name,
            'email' => 'nuevo-correo@example.com',
            'role' => User::ROLE_CLIENTE,
            'sucursal_id' => null,
        ])->assertOk();

        $employee->refresh();

        $this->assertSame('nuevo-correo@example.com', $employee->email);
        $this->assertNull($employee->email_verified_at);
        $this->assertSame(0, $employee->tokens()->count());
    }
}
