<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class KitchenBoardController extends Controller
{
    public function index()
    {
        // Valida rol mínimo en la vista (y oculta el acceso si no es admin/cocina)
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['admin','cocina']), 403);

        return view('kitchen.index', [
            'apiBase' => rtrim(config('services.latina_api.base_url'), '/'), // p.ej. http://127.0.0.1:8001/api
            'apiToken'=> session('token'), // Bearer token de tu login actual
        ]);
    }
}