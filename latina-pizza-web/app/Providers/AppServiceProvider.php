<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
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
        View::composer('*', function ($view) {
            $carritoCount = 0;
            $token = Session::get('token');

            if ($token) {
                $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/carrito');

                if ($response->successful()) {
                    $carrito = $response->json();
                    $carritoCount = collect($carrito['productos'] ?? [])->sum(function ($item) {
                        return $item['pivot']['cantidad'] ?? 0;
                    });
                }
            }

            $view->with('carritoCount', $carritoCount);
        });
    }
}
