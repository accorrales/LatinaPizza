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

        // Queue every valid address: account lookup/SMTP must not disclose users
        // through response timing. Production requires an asynchronous connection.
        SendPasswordRecoveryCode::dispatch(strtolower(trim($request->input('email'))), now()->timestamp);

        return response()->json(['status' => PasswordRecovery::STATUS]);
    }
}
