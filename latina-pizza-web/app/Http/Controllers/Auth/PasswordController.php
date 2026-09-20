<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $request->user()->id)
                ->delete();
        }

        try {
            $response = Http::acceptJson()->timeout(10)->post(config('app.api_url').'/api/login', [
                'email' => $request->user()->email,
                'password' => $validated['password'],
                'token_name' => 'web-session',
            ]);
        } catch (\Throwable) {
            $response = null;
        }

        if (! $response?->successful() || ! $response->json('token')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Contraseña actualizada. Inicie sesión nuevamente.');
        }

        $request->session()->put('token', $response->json('token'));

        return back()->with('status', 'password-updated');
    }
}
