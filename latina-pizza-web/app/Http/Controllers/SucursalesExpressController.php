<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SucursalesExpressController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/'); // ej: http://localhost:8001/api
    }

    // Lista sucursales cercanas a la dirección
    public function index(Request $r)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login');

        $r->validate(['direccion_usuario_id' => 'required|integer']);

        $resp = Http::withToken($token)->get("{$this->apiBase}/sucursales/cercanas", [
            'direccion_usuario_id' => $r->direccion_usuario_id,
        ])->throw();

        $direccion  = $resp->json('direccion');
        $sucursales = $resp->json('sucursales') ?? [];

        return view('pedido.sucursales_express', compact('direccion','sucursales'));
    }

    // Fija Express con dirección + sucursal, y pasa al catálogo
    public function seleccionar(Request $r)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login');

        $data = $r->validate([
            'direccion_usuario_id' => 'required|integer',
            'sucursal_id'          => 'required|integer',
        ]);

        Http::withToken($token)->post("{$this->apiBase}/carrito/metodo-entrega", [
            'tipo'                 => 'express',
            'direccion_usuario_id' => $data['direccion_usuario_id'],
            'sucursal_id'          => $data['sucursal_id'],
        ])->throw();

        return redirect()->route('catalogo.index')->with('ok', 'Sucursal seleccionada. ¡A ordenar!');
    }
}
