<?php

use App\Models\User;

test('customers cannot open the administration panel', function () {
    $user = User::factory()->create(['role' => 'cliente']);

    $this->actingAs($user)->get('/admin/productos')->assertForbidden();
});

test('kitchen users can open the kitchen board but not administration', function () {
    $user = User::factory()->create(['role' => 'cocina']);

    $this->actingAs($user)->get('/kitchen')->assertOk();
    $this->actingAs($user)->get('/admin/productos')->assertForbidden();
});

test('unverified customers are sent to the verification screen', function () {
    $user = User::factory()->unverified()->create(['role' => 'cliente']);

    $this->actingAs($user)
        ->get('/carrito')
        ->assertRedirect(route('verification.notice'));
});
