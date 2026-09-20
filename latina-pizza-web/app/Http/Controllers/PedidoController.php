<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PedidoController extends Controller
{
    public function detalleHistorial($id)
    {
        $token = session('token');

        if (!$token) {
            return abort(403, 'Token no disponible. Inicia sesión nuevamente.');
        }

        $response = Http::withToken($token)
            ->get("http://localhost:8001/api/pedidos/{$id}");

        if ($response->successful()) {
            $pedido = $response->json();
            return view('pedidos.detalle', compact('pedido'));
        }

        return abort(404, 'Pedido no encontrado.');
    }

    public function vistaHistorial(Request $request)
    {
        $token = session('token');

        if (!$token) {
            return redirect()->route('login');
        }

        $response = Http::withToken($token)
            ->get(config('app.api_url') . '/api/mis-pedidos');

        if ($response->successful()) {
            $pedidos = $response->json();
            return view('pedidos.mis_pedidos', compact('pedidos'));
        }

        dd($response->status(), $response->body());

        return abort(403, 'No se pudo cargar el historial');
    }
    public function detallePromocion($id)
    {
        $token = session('token');

        if (!$token) {
            return abort(403, 'Token no disponible. Inicia sesión nuevamente.');
        }

        $response = Http::withToken($token)
            ->get("http://localhost:8001/api/detalle-pedido-promocion/{$id}/detalles");

        if ($response->successful()) {
            $pedido = $response->json(); // 👈 renombralo aquí
            return view('pedidos.detalle_promocion', compact('pedido'));
        }
        return abort(404, 'No se encontraron detalles de la promoción para este pedido.');
    }
}
