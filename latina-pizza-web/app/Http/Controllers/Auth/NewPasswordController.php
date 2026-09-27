<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordRecoveryClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('recovery_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function store(Request $request, PasswordRecoveryClient $client): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'regex:/\\A[0-9]{6}\\z/'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed', function ($attribute, $value, $fail) {
                if (is_string($value) && strlen($value) > 72) {
                    $fail('La contraseña es demasiado larga. Usá una frase más corta.');
                }
            }],
        ], [
            'email.required' => 'Ingresá tu correo electrónico.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'code.required' => 'Ingresá el código de seis dígitos.',
            'code.regex' => 'El código debe tener seis dígitos.',
            'password.required' => 'Ingresá tu nueva contraseña.',
            'password.min' => 'Usá una contraseña de al menos 12 caracteres.',
            'password.max' => 'Usá una contraseña de hasta 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);
        $data['email'] = strtolower(trim($data['email']));
        $data['password_confirmation'] = $request->input('password_confirmation');
        $client->post('reset-password', $data);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status',
            'Contraseña actualizada. Iniciá sesión con tu nueva contraseña. Las sesiones anteriores se cerraron.');
    }
}
