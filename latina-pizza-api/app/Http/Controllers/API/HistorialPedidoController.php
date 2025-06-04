<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistorialPedido;

class HistorialPedidoController extends Controller
{
    public function index($pedidoId)
    {
        $historial = HistorialPedido::where('pedido_id', $pedidoId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($historial);
    }
}
