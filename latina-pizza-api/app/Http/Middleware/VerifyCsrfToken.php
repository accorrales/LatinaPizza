<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'api/*',          // ⛑️ saca TODO el API del CSRF
        // o más fino: 'api/kitchen/*'
    ];
}
