<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        foreach (['recovery-send' => 3, 'recovery-reset' => 10] as $name => $attempts) {
            RateLimiter::for($name, function (Request $request) use ($name, $attempts) {
                $email = $request->input('email');
                $key = hash('sha256', is_string($email) ? strtolower(trim($email)) : 'invalid');

                return [
                    // Web proxies share an IP; per-email limits apply across all clients.
                    Limit::perMinute(100)->by($name.':ip:'.$request->ip()),
                    Limit::perMinutes(15, $attempts)->by($name.':email:'.$key),
                ];
            });
        }
    }
}
