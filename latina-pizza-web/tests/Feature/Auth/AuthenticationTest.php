<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    Http::fake([
        '*/api/login' => Http::response(['token' => 'test-token', 'user' => $user->toArray()]),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
    $response->assertSessionHas('token', 'test-token');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    Http::fake(['*/api/logout' => Http::response(['message' => 'ok'])]);

    $response = $this->actingAs($user)->withSession(['token' => 'test-token'])->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
