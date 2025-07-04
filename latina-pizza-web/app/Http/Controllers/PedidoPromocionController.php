<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PedidoPromocionController extends Controller
{
    public function mostrarResumen($id)
    {
        $response = Http::get("http://localhost:8001/api/detalle-pedido-promocion/{$id}/detalles");

        if ($response->successful()) {
            $data = $response->json();
            return view('pedidos.promocion_resumen', compact('data'));
        } else {
            abort(404, 'Pedido no encontrado');
        }
    }
}
