<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Simula la API externa caída: toda llamada HTTP lanza ConnectionException.
 * Verifica que las páginas degraden con gracia (nunca 500 ni cuelgue).
 */
function fakeApiDown(): void
{
    Http::preventStrayRequests();
    Http::fake(function () {
        throw new ConnectionException('Simulated API outage');
    });
}

test('home carga aunque la API esté caída', function () {
    fakeApiDown();

    $this->get('/')->assertOk();
});

test('catálogo carga aunque la API esté caída', function () {
    fakeApiDown();

    $this->get('/catalogo')->assertOk();
});

test('admin usuarios no revienta (500) con la API caída', function () {
    fakeApiDown();

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withSession(['token' => 'fake-token'])
        ->get('/admin/usuarios');

    expect($response->status())->toBeLessThan(500);
});

test('express no revienta (500) con la API caída', function () {
    fakeApiDown();

    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)
        ->withSession(['token' => 'fake-token'])
        ->get('/express');

    expect($response->status())->toBeLessThan(500);
});
