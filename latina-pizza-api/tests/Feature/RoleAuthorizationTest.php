<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'cliente']));

        $this->getJson('/api/admin/usuarios')->assertForbidden();
    }

    public function test_admin_can_access_admin_endpoints(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->getJson('/api/admin/usuarios')->assertOk();
    }

    public function test_customer_cannot_create_an_order_outside_checkout(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'cliente']));

        $this->postJson('/api/pedidos', [])->assertNotFound();
    }

    public function test_unverified_customer_cannot_use_the_cart(): void
    {
        Sanctum::actingAs(User::factory()->unverified()->create(['role' => 'cliente']));

        $this->getJson('/api/carrito')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Debe verificar su correo electrónico antes de continuar.');
    }
}
