<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_RESET_STATUS = 'If an account exists for that email address, we have sent a password reset link.';

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/api/forgot-password', ['email' => $user->email]);

        $response
            ->assertOk()
            ->assertJson(['status' => self::GENERIC_RESET_STATUS]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_does_not_reveal_account_existence(): void
    {
        Notification::fake();

        $response = $this->post('/api/forgot-password', ['email' => 'missing@example.com']);

        $response
            ->assertOk()
            ->assertJson(['status' => self::GENERIC_RESET_STATUS]);

        Notification::assertNothingSent();
    }

    public function test_reset_link_requests_are_rate_limited(): void
    {
        Notification::fake();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/api/forgot-password', ['email' => 'missing@example.com'])
                ->assertOk();
        }

        $this->post('/api/forgot-password', ['email' => 'missing@example.com'])
            ->assertStatus(429);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
            $response = $this->post('/api/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertStatus(200);

            return true;
        });
    }
}
