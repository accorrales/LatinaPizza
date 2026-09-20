<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HistorialPedido;
use App\Models\Pedido;
use Illuminate\Http\Request;

class HistorialPedidoController extends Controller
{
    public function index(Request $request, $pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);
        $user = $request->user();

        abort_unless(
            $pedido->user_id === $user->id || $user->hasAnyRole('admin', 'cocina'),
            403,
            'No autorizado'
        );

        $historial = HistorialPedido::where('pedido_id', $pedidoId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($historial);
    }
}
