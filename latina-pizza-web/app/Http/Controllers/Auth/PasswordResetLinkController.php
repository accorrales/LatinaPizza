<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordRecoveryClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request, PasswordRecoveryClient $client): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']], [
            'email.required' => 'Ingresá tu correo electrónico.',
            'email.email' => 'Ingresá un correo electrónico válido.',
        ]);
        $email = strtolower(trim($request->input('email')));
        $client->post('forgot-password', ['email' => $email]);
        $request->session()->put('recovery_email', $email);
        $request->session()->put('recovery_resend_at', now()->addMinute()->timestamp);

        return redirect()->route('password.reset')->with('status',
            'Si existe una cuenta con ese correo, recibirás un código de recuperación. Revisá también la carpeta de spam.');
    }
}
