<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PromocionesController extends Controller
{
    public function index()
        {
            $response = Http::get('http://127.0.0.1:8001/api/promociones');

            if ($response->successful()) {
                $promociones = $response->json()['data'];
                return view('promociones.index', compact('promociones'));
            }

            return back()->with('error', 'No se pudieron cargar las promociones');
        }
}