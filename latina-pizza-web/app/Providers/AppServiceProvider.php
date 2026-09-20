<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::globalOptions([
            'connect_timeout' => 3,
            'timeout' => 10,
        ]);

        View::composer('layouts.app', function ($view) {
            $carritoCount = 0;
            $token = Session::get('token');

            if ($token) {
                try {
                    $apiBase = rtrim(config('services.latina_api.base_url'), '/');
                    $response = Http::withToken($token)->timeout(3)->get("{$apiBase}/carrito");

                    if ($response->successful()) {
                        $carritoCount = collect($response->json('data.items', []))
                            ->sum(fn ($item) => (int) ($item['cantidad'] ?? 1));
                    }
                } catch (\Throwable) {
                    $carritoCount = 0;
                }
            }

            $view->with('carritoCount', $carritoCount);
        });
    }
}
