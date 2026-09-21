<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        if (app()->isProduction()) {
            $connectSources = [
                "'self'",
                'https://api.maptiler.com',
                'https://*.maptiler.com',
                'https://api.stripe.com',
                'https://*.stripe.com',
                'https://*.stripe.network',
            ];

            if ($apiOrigin = $this->apiOrigin()) {
                $connectSources[] = $apiOrigin;
            }

            $directives = [
                "default-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'self'",
                "form-action 'self'",
                "script-src 'self' https://js.stripe.com https://cdn.jsdelivr.net",
                "script-src-attr 'none'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data:",
                'connect-src '.implode(' ', array_values(array_unique($connectSources))),
                'frame-src https://js.stripe.com https://hooks.stripe.com https://*.stripe.com https://*.stripe.network',
                "worker-src 'self' blob:",
                "manifest-src 'self'",
                'upgrade-insecure-requests',
            ];

            $response->headers->set('Content-Security-Policy', implode('; ', $directives));

            if ($request->isSecure()) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        }

        return $response;
    }

    private function apiOrigin(): ?string
    {
        $url = (string) config('app.api_url');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (! $scheme || ! $host) {
            return null;
        }

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }
}
