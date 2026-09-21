<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request without revealing whether
     * the submitted email belongs to an account.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'status' => __('If an account exists for that email address, we have sent a password reset link.'),
        ]);
    }
}
