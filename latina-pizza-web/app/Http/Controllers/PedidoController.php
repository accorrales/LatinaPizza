<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{
    public function detalleHistorial($id)
    {
        $token = session('token');

        if (! $token) {
            return abort(403, 'Token no disponible. Inicia sesión nuevamente.');
        }

        $response = Http::withToken($token)
            ->get($this->apiUrl("/pedidos/{$id}"));

        if ($response->successful()) {
            $pedido = $response->json();

            return view('pedidos.detalle', compact('pedido'));
        }

        return abort(404, 'Pedido no encontrado.');
    }

    public function vistaHistorial(Request $request)
    {
        $token = session('token');

        if (! $token) {
            return redirect()->route('login');
        }

        $response = Http::withToken($token)
            ->get($this->apiUrl('/mis-pedidos'), [
                'page' => max(1, $request->integer('page', 1)),
                'per_page' => 20,
            ]);

        if ($response->successful()) {
            $pedidos = $response->json('data', []);
            $pagination = [
                'current_page' => (int) $response->json('current_page', 1),
                'last_page' => (int) $response->json('last_page', 1),
            ];

            return view('pedidos.mis_pedidos', compact('pedidos', 'pagination'));
        }

        return abort($response->status() === 401 ? 401 : 503, 'No se pudo cargar el historial en este momento.');
    }

    public function detallePromocion($id)
    {
        $token = session('token');

        if (! $token) {
            return abort(403, 'Token no disponible. Inicia sesión nuevamente.');
        }

        $response = Http::withToken($token)
            ->get($this->apiUrl("/detalle-pedido-promocion/{$id}/detalles"));

        if ($response->successful()) {
            $pedido = $response->json();

            return view('pedidos.detalle_promocion', compact('pedido'));
        }

        return abort(404, 'No se encontraron detalles de la promoción para este pedido.');
    }
}
