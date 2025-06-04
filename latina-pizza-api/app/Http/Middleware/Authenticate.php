<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Redirigir si el usuario no está autenticado (para APIs responde con 401).
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }

}


