<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendPasswordRecoveryCode;
use App\Models\User;
use App\Notifications\PasswordChanged;
use App\Notifications\PasswordRecoveryCode;
use App\Services\PasswordRecovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function issue(User $user): string
    {
        Notification::fake();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();

        return Notification::sent($user, PasswordRecoveryCode::class)->last()->code;
    }

    private function payload(User $user, string $code): array
    {
        return ['email' => $user->email, 'code' => $code,
            'password' => 'Una frase nueva 2026!', 'password_confirmation' => 'Una frase nueva 2026!'];
    }

    public function test_known_and_unknown_addresses_have_identical_queued_responses(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        foreach ([$user->email, 'missing@example.com'] as $email) {
            $this->postJson('/api/forgot-password', ['email' => $email])
                ->assertOk()->assertExactJson(['status' => PasswordRecovery::STATUS]);
        }
        Queue::assertPushed(SendPasswordRecoveryCode::class, 2);
        Queue::assertPushedOn('password-recovery', SendPasswordRecoveryCode::class);
    }

    public function test_unknown_address_sends_no_email(): void
    {
        Notification::fake();
        $this->postJson('/api/forgot-password', ['email' => 'missing@example.com'])->assertOk();
        Notification::assertNothingSent();
    }

    public function test_code_is_six_digits_and_not_stored_in_plaintext(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $row = DB::table('password_reset_codes')->first();
        $this->assertNotSame($code, $row->digest);
        $this->assertSame(64, strlen($row->digest));
        $this->assertEquals(now()->addMinutes(10)->format('Y-m-d H:i:s'), $row->expires_at);
        $mail = (new PasswordRecoveryCode($code))->toMail($user);
        $this->assertStringContainsString($code, implode(' ', $mail->introLines));
    }

    public function test_reset_changes_password_revokes_access_and_cannot_be_replayed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->createToken('phone');
        $other->createToken('phone');
        DB::table('sessions')->insert(['id' => 'old-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        $remember = $user->remember_token;
        $code = $this->issue($user);
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertOk();
        Notification::assertSentTo($user, PasswordChanged::class);
        $this->assertTrue(Hash::check('Una frase nueva 2026!', $user->fresh()->password));
        $this->assertNotEquals($remember, $user->fresh()->remember_token);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(1, $other->tokens()->count());
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
        $this->assertDatabaseMissing('password_reset_codes', ['user_id' => $user->id]);
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertUnprocessable();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Una frase nueva 2026!'])->assertOk();
    }

    public function test_expiry_is_enforced_at_ten_minutes(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $this->travel(10)->minutes();
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_five_failed_attempts_lock_the_code_even_with_different_ips(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $wrong = $code === '000000' ? '111111' : '000000';
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($i + 1)])
                ->postJson('/api/reset-password', $this->payload($user, $wrong))->assertUnprocessable();
        }
        $this->assertDatabaseHas('password_reset_codes', ['user_id' => $user->id, 'attempts' => 5]);
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertUnprocessable();
    }

    public function test_resend_cooldown_then_replacement_invalidates_previous_code(): void
    {
        $user = User::factory()->create();
        $old = $this->issue($user);
        $digest = DB::table('password_reset_codes')->value('digest');
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        Notification::assertSentToTimes($user, PasswordRecoveryCode::class, 1);
        $this->assertSame($digest, DB::table('password_reset_codes')->value('digest'));
        $this->travel(61)->seconds();
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertOk();
        $new = Notification::sent($user, PasswordRecoveryCode::class)->last()->code;
        $this->assertNotSame($old, $new);
        $this->postJson('/api/reset-password', $this->payload($user, $old))->assertUnprocessable();
        $this->postJson('/api/reset-password', $this->payload($user, $new))->assertOk();
    }

    public function test_per_email_send_limit_cannot_be_bypassed_by_case_or_ip(): void
    {
        Queue::fake();
        for ($i = 0; $i < 3; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($i + 1)])
                ->postJson('/api/forgot-password', ['email' => 'test@example.com'])->assertOk();
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.99'])
            ->postJson('/api/forgot-password', ['email' => 'TEST@EXAMPLE.COM'])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_password_change_invalidates_outstanding_code(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $user->update(['password' => Hash::make('Changed elsewhere!')]);
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertUnprocessable();
    }

    public function test_code_cannot_reset_another_account(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $other = User::factory()->create();
        $this->postJson('/api/reset-password', $this->payload($other, $code))->assertUnprocessable();
        $this->assertTrue(Hash::check('password', $other->fresh()->password));
    }

    public function test_invalid_password_or_confirmation_does_not_consume_code(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $this->postJson('/api/reset-password', array_merge($this->payload($user, $code), ['password' => 'short']))
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/api/reset-password', array_merge($this->payload($user, $code), ['password_confirmation' => 'different']))
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertDatabaseHas('password_reset_codes', ['user_id' => $user->id, 'attempts' => 0]);
        $this->postJson('/api/reset-password', $this->payload($user, $code))->assertOk();
    }

    public function test_invalid_input_and_old_tokens_are_rejected(): void
    {
        $this->postJson('/api/forgot-password', ['email' => ['bad']])->assertUnprocessable();
        $user = User::factory()->create();
        $this->postJson('/api/reset-password', $this->payload($user, '12345'))->assertUnprocessable();
        $data = $this->payload($user, '123456');
        unset($data['code']);
        $data['token'] = 'old-link-token';
        $this->postJson('/api/reset-password', $data)->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_stale_queued_requests_do_not_send_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        (new SendPasswordRecoveryCode($user->email, now()->subMinutes(11)->timestamp))->handle(app(PasswordRecovery::class));
        Notification::assertNothingSent();
    }

    public function test_multibyte_password_cannot_exceed_bcrypt_byte_limit(): void
    {
        $user = User::factory()->create();
        $code = $this->issue($user);
        $password = str_repeat('ñ', 40);
        $this->postJson('/api/reset-password', array_merge($this->payload($user, $code), [
            'password' => $password, 'password_confirmation' => $password,
        ]))->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertDatabaseHas('password_reset_codes', ['user_id' => $user->id, 'attempts' => 0]);
    }

    public function test_reset_requests_are_rate_limited_even_without_an_account(): void
    {
        $data = ['email' => 'missing@example.com', 'code' => '123456',
            'password' => 'Una frase nueva 2026!', 'password_confirmation' => 'Una frase nueva 2026!'];
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/reset-password', $data)->assertUnprocessable();
        }
        $this->postJson('/api/reset-password', $data)->assertStatus(429);
    }
}
