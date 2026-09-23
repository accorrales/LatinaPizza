<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordChanged;
use App\Notifications\PasswordRecoveryCode;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordRecovery
{
    public const STATUS = 'Si existe una cuenta con ese correo, recibirás un código de recuperación. Revisá también la carpeta de spam.';

    public const INVALID_CODE = 'El código no es válido o venció. Revisalo o solicitá uno nuevo.';

    public const MINUTES = 10;

    public function send(string $email): void
    {
        // Lock the user even on first issuance, serializing resend and reset.
        $delivery = DB::transaction(function () use ($email) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            if (! $user) {
                return null;
            }

            $existing = DB::table('password_reset_codes')->where('user_id', $user->id)->first();
            if ($existing && now()->subMinute()->lt($existing->created_at)) {
                return null;
            }

            do {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while ($existing && hash_equals($existing->digest, $this->digest($user, $code)));
            DB::table('password_reset_codes')->updateOrInsert(['user_id' => $user->id], [
                'digest' => $this->digest($user, $code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::MINUTES),
                'created_at' => now(),
            ]);

            return [$user, $code];
        });

        if ($delivery) {
            [$user, $code] = $delivery;
            $user->notify(new PasswordRecoveryCode($code));
        }
    }

    public function reset(string $email, string $code, string $password): bool
    {
        $user = DB::transaction(function () use ($email, $code, $password) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            if (! $user) {
                return null;
            }

            $query = DB::table('password_reset_codes')->where('user_id', $user->id);
            $challenge = $query->first();
            if (! $challenge || now()->gte($challenge->expires_at) || $challenge->attempts >= 5) {
                return null;
            }

            if (! hash_equals($challenge->digest, $this->digest($user, $code))) {
                // Return, do not throw: failed attempts must commit.
                $query->increment('attempts');

                return null;
            }

            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $query->delete();

            return $user;
        });

        if (! $user) {
            return false;
        }

        event(new PasswordReset($user));

        try {
            $user->notify(new PasswordChanged);
        } catch (\Throwable) {
            // The password already changed. Never turn success into a retryable reset.
            Log::warning('Password change notification could not be queued.', ['user_id' => $user->id]);
        }

        return true;
    }

    private function digest(User $user, string $code): string
    {
        // Bind to current credentials; a password/email change invalidates the code.
        // The key protects six-digit codes against offline DB-only guessing.
        return hash_hmac('sha256', implode('|', [$user->id, $user->email, $user->password, $code]), config('app.key'));
    }
}
