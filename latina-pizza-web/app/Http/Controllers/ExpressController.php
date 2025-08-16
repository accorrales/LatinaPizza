<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ExpressController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/'); // ej: http://localhost:8001/api
    }

    public function index()
    {
        $token = Session::get('token');           // ← asegúrate que guardas este token al loguear
        if (!$token) {
            return redirect()->route('login')->withErrors('Inicia sesión para ver tus direcciones.');
        }

        $direcciones = [];
        try {
            $resp = Http::withToken($token)->get("{$this->apiBase}/direcciones");
            if ($resp->ok()) {
                $direcciones = $resp->json('data') ?? [];
            } elseif ($resp->status() === 401) {
                return redirect()->route('login')->withErrors('Sesión expirada. Ingresa de nuevo.');
            } else {
                session()->flash('error', 'No se pudieron cargar las direcciones.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error de conexión con el API.');
        }

        // Tu vista está en resources/views/pedido/express.blade.php
        return view('pedido.express', compact('direcciones'));
    }

    public function store(Request $r)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login');

        $payload = $r->validate([
            'nombre'            => 'required|string|max:255',
            'direccion_exacta'  => 'required|string|max:255',
            'provincia'         => 'required|string|max:255',
            'canton'            => 'required|string|max:255',
            'distrito'          => 'required|string|max:255',
            'telefono_contacto' => 'required|string|max:50',
            'referencias'       => 'nullable|string|max:255',
            'latitud'           => 'nullable|numeric|between:-90,90',
            'longitud'          => 'nullable|numeric|between:-180,180',
        ]);

        Http::withToken($token)->post("{$this->apiBase}/direcciones", $payload)->throw();

        return redirect()->route('express.index')->with('ok','Dirección guardada');
    }

    public function seleccionar(Request $r)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login');

        $data = $r->validate(['direccion_usuario_id' => 'required|integer']);

        Http::withToken($token)->post("{$this->apiBase}/carrito/metodo-entrega", [
            'tipo'                 => 'express',
            'direccion_usuario_id' => $data['direccion_usuario_id'],
        ])->throw();

        return redirect()->route('catalogo.index')->with('ok','Entrega Express seleccionada');
    }
}

