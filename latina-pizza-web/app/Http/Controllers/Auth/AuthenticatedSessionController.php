<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Throwable;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Login normal de Laravel
        $request->authenticate();
        $request->session()->regenerate();

        // Llamada a login de la API para obtener el token
        try {
            $response = Http::acceptJson()->timeout(10)->post(config('app.api_url') . '/api/login', [
                'email' => $request->email,
                'password' => $request->password,
                'token_name' => 'web-session',
            ]);
        } catch (Throwable) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'No se pudo conectar con el servicio de Latina Pizza. Intente nuevamente.',
            ]);
        }

        if (!$response->successful() || !$response->json('token')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages([
                'email' => 'No fue posible completar el inicio de sesión.',
            ]);
        }

        Session::put('token', $response->json('token'));

        if (!$request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $token = $request->session()->get('token');
        if ($token) {
            try {
                Http::withToken($token)
                    ->acceptJson()
                    ->timeout(5)
                    ->post(config('app.api_url') . '/api/logout');
            } catch (Throwable) {
                // Local logout must always finish, even if the API is unavailable.
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
