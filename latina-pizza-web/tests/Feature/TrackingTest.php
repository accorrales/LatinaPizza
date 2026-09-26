<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('tracking is restricted to verified staff', function () {
    $this->get('/rastreo')->assertRedirect('/login');
    $this->actingAs(User::factory()->create(['role' => 'cliente']))->get('/rastreo')->assertForbidden();
    $this->getJson('/rastreo/pedidos')->assertForbidden();
    $this->actingAs(User::factory()->unverified()->create(['role' => 'cocina']))->get('/rastreo')->assertRedirect(route('verification.notice'));
});

test('staff can open tracking and proxy uses server side token', function () {
    Http::fake(['*' => Http::response(['data' => [], 'meta' => ['total' => 0]], 200)]);
    $this->actingAs(User::factory()->create(['role' => 'cocina']))->get('/rastreo')->assertOk()->assertSee('Rastreo de pedidos');
    $this->withSession(['token' => 'private-token'])->getJson('/rastreo/pedidos?estado=activos&sucursal_id=99')
        ->assertOk()->assertJsonPath('meta.total', 0);
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer private-token')
        && str_contains($request->url(), '/tracking/orders') && ! str_contains($request->url(), 'sucursal_id'));
});

test('tracking reports upstream failures and missing token', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))->getJson('/rastreo/pedidos')->assertUnauthorized();
    Http::fake(['*' => Http::response([], 503)]);
    $this->withSession(['token' => 'private-token'])->getJson('/rastreo/pedidos')->assertStatus(503);
});

test('customer tracking page rejects an order the API says is not theirs', function () {
    $this->actingAs(User::factory()->create(['role' => 'cliente']));
    Http::fake(['*' => Http::response(['message' => 'No autorizado'], 403)]);
    $this->withSession(['token' => 'customer-token'])->get('/mis-pedidos/12/rastreo')->assertForbidden();
    $this->getJson('/mis-pedidos/12/ubicacion')->assertForbidden();
});

test('customer tracking page renders once the API authorizes the order', function () {
    $this->actingAs(User::factory()->create(['role' => 'cliente']));
    Http::fake(['*' => Http::response(['data' => ['id' => 12, 'estado' => 'en_camino', 'location' => null]])]);
    $this->withSession(['token' => 'customer-token'])->get('/mis-pedidos/12/rastreo')->assertOk()->assertSee('live-tracking')->assertSee('Pedido #12');
    $this->getJson('/mis-pedidos/12/ubicacion')->assertOk()->assertJsonPath('data.estado', 'en_camino');
});

test('only delivery staff can use the phone page and send coordinates through the proxy', function () {
    $this->actingAs(User::factory()->create(['role' => 'cliente']));
    $this->get('/repartos')->assertForbidden();
    $this->postJson('/repartos/12/ubicacion', [])->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'delivery']));
    $this->get('/repartos')->assertOk()->assertSee('Compartir mi ubicación');
    Http::fake(['*' => Http::response(['data' => []])]);
    $this->withSession(['token' => 'driver-token'])->postJson('/repartos/12/ubicacion', [
        'latitude' => 9.93, 'longitude' => -84.08, 'accuracy' => 10,
        'recorded_at' => '2026-09-26T18:00:00Z', 'delivery_user_id' => 999,
    ])->assertOk();
    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && str_ends_with($request->url(), '/delivery/orders/12/location')
        && $request->hasHeader('Authorization', 'Bearer driver-token')
        && $request['latitude'] === 9.93 && ! isset($request['delivery_user_id']));
});

test('location proxy preserves validation and closed-order errors', function () {
    $this->actingAs(User::factory()->create(['role' => 'delivery']));
    Http::fake(['*' => Http::response(['message' => 'El pedido no está en camino.'], 409)]);
    $this->withSession(['token' => 'driver-token'])->postJson('/repartos/12/ubicacion', [])
        ->assertStatus(409)->assertJsonPath('message', 'El pedido no está en camino.');
});
