<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AnalyticsBoardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->role === 'admin', 403); // doble check server-side

        return view('analytics.ventas', [
            'apiBase'  => rtrim(config('services.latina_api.base_url'), '/'), // ej: http://127.0.0.1:8001/api
            'apiToken' => session('token'), // si no lo usás, déjalo null y usa localStorage en el JS
        ]);
    }
}