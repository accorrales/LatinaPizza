<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordRecovery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    public function store(Request $request, PasswordRecovery $recovery): JsonResponse
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
            'code.required' => 'Ingresá el código de seis dígitos.',
            'code.regex' => 'El código debe tener seis dígitos.',
            'password.min' => 'Usá una contraseña de al menos 12 caracteres.',
            'password.max' => 'Usá una contraseña de hasta 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if (! $recovery->reset(strtolower(trim($data['email'])), $data['code'], $data['password'])) {
            throw ValidationException::withMessages(['code' => PasswordRecovery::INVALID_CODE]);
        }

        return response()->json(['status' => 'Contraseña actualizada. Iniciá sesión con tu nueva contraseña.']);
    }
}
