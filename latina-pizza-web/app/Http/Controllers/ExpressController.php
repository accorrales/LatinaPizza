<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ExpressController extends Controller
{
    public function index()
    {
        $token = Session::get('token');           // ← asegúrate que guardas este token al loguear
        if (! $token) {
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

        $payload = $r->validate([
            'nombre' => 'required|string|max:255',
            'direccion_exacta' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'canton' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'telefono_contacto' => 'required|string|max:50',
            'referencias' => 'nullable|string|max:255',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ]);

        $resp = Http::withToken($token)
            ->post("{$this->apiBase}/direcciones", $payload)
            ->throw();

        $dirId = data_get($resp->json(), 'data.id');

        // 👉 Ir a sucursales cercanas para esa dirección
        return redirect()->route('sucursales.express', ['direccion_usuario_id' => $dirId]);
    }

    public function seleccionar(Request $r)
    {
        // 1) Validar que venga la dirección
        $data = $r->validate([
            'direccion_usuario_id' => 'required|integer',
        ]);

        // 2) NO llamamos al API aquí. Redirigimos a la lista de sucursales cercanas
        //    para esa dirección. Allí el usuario elige una sucursal y recién entonces
        //    posteamos ambos IDs al API.
        return redirect()->route('sucursales.express', $data);
    }
}
