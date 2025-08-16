<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class ExpressController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        // Ej: http://127.0.0.1:8001/api  (definido en .env como LATINA_API_BASE_URL)
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/');
    }

    /**
     * GET /express
     * Lista direcciones del usuario autenticado usando el API.
     */
    public function index()
    {
        $token = Session::get('token');

        $direcciones = [];
        try {
            $resp = Http::withToken($token)->get("{$this->apiBase}/direcciones");
            $direcciones = $resp->json('data') ?? [];
        } catch (\Throwable $e) {
            // Si hay error de API, mostramos vacío pero con aviso
            session()->flash('error', 'No se pudieron cargar las direcciones. Intenta de nuevo.');
        }

        return view('entrega.express', compact('direcciones'));
    }

    /**
     * POST /express/direcciones
     * Crea una nueva dirección en el API.
     */
    public function store(Request $r)
    {
        $token = Session::get('token');

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

        Http::withToken($token)
            ->post("{$this->apiBase}/direcciones", $payload)
            ->throw();

        return redirect()->route('express.index')->with('ok', 'Dirección guardada');
    }

    /**
     * POST /express/seleccionar
     * Selecciona una dirección y fija el método de entrega del carrito a "express".
     */
    public function seleccionar(Request $r)
    {
        $token = Session::get('token');

        $data = $r->validate([
            'direccion_usuario_id' => 'required|integer',
        ]);

        Http::withToken($token)
            ->post("{$this->apiBase}/carrito/metodo-entrega", [
                'tipo'                 => 'express',
                'direccion_usuario_id' => $data['direccion_usuario_id'],
            ])
            ->throw();

        // Ir al catálogo a continuar el pedido
        return redirect()->route('catalogo.index')->with('ok', 'Entrega Express seleccionada');
    }
}
