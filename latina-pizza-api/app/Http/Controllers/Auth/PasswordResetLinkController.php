<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordRecoveryCode;
use App\Services\PasswordRecovery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        abort_if(app()->isProduction() && in_array(config('queue.default'), ['sync', 'null'], true), 503);
        abort_if(app()->isProduction() && in_array(config('mail.default'), ['log', 'array'], true), 503);

        $email = strtolower(trim($request->input('email')));

        // Local development should work without requiring a separate queue worker.
        // Production remains asynchronous so account lookup/SMTP timing cannot
        // disclose whether an address belongs to an existing account.
        if (app()->isLocal()) {
            SendPasswordRecoveryCode::dispatchSync($email, now()->timestamp);
        } else {
            SendPasswordRecoveryCode::dispatch($email, now()->timestamp);
        }

        return response()->json(['status' => PasswordRecovery::STATUS]);
    }
}
