<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

const GENERIC_RESET_STATUS = 'If an account exists for that email address, we have sent a password reset link.';

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    $response
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_STATUS);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('unknown email does not reveal account existence', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'missing@example.com']);

    $response
        ->assertRedirect()
        ->assertSessionHas('status', GENERIC_RESET_STATUS)
        ->assertSessionHasNoErrors();

    Notification::assertNothingSent();
});

test('reset link requests are rate limited', function () {
    Notification::fake();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/forgot-password', ['email' => 'missing@example.com'])
            ->assertRedirect();
    }

    $this->post('/forgot-password', ['email' => 'missing@example.com'])
        ->assertStatus(429);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
