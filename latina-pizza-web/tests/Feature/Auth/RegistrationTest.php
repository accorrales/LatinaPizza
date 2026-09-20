<?php

use Illuminate\Support\Facades\Http;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Http::fake([
        '*/api/login' => Http::response(['token' => 'test-token']),
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
    $response->assertSessionHas('token', 'test-token');
});
