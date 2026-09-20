<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login (autenticación y generación de token).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'token_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $tokenName = $request->string('token_name')->toString() ?: 'auth_token';
        $user->tokens()->where('name', $tokenName)->delete();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($tokenName)->plainTextToken,
        ]);
    }

    /**
     * Logout (revoca el token actual).
     */
    public function destroy(Request $request): JsonResponse
    {
        // Elimina el token que se está usando
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
