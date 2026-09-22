<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        try {
            $response = Http::connectTimeout(3)->acceptJson()->timeout(5)->post(config('app.api_url').'/api/login', [
                'email' => $request->email,
                'password' => $request->password,
                'token_name' => 'web-session',
            ]);
        } catch (Throwable) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'La cuenta fue creada, pero el servicio no respondió. Intente iniciar sesión nuevamente.',
            ]);
        }

        if (! $response->successful() || ! $response->json('token')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'La cuenta fue creada, pero no se pudo iniciar sesión. Intente nuevamente.',
            ]);
        }

        $request->session()->put('token', $response->json('token'));

        return redirect(route('verification.notice', absolute: false));
    }
}
