<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_an_empty_cart(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'cliente']));

        $this->postJson('/api/checkout', ['metodo_pago' => 'efectivo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('carrito');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create(['role' => 'cliente']);
        $intruder = User::factory()->create(['role' => 'cliente']);
        $pedido = Pedido::create([
            'user_id' => $owner->id,
            'total' => 5000,
            'estado' => 'pendiente',
        ]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/pedidos/{$pedido->id}")->assertForbidden();
    }
}
