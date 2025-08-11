<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoTipoController extends Controller
{
    public function guardar(Request $request)
    {
        $user = Auth::user();

        $tipo = $request->input('tipo');

        if ($tipo === null) {
            session()->forget('tipo_pedido');
            return response()->json(['message' => 'Tipo de pedido eliminado']);
        }

        if (!in_array($tipo, ['pickup', 'express'])) {
            return response()->json(['error' => 'Tipo de pedido inválido'], 400);
        }

        session(['tipo_pedido' => $tipo]);

        return response()->json([
            'message' => 'Tipo de pedido guardado en sesión',
            'user_id' => $user?->id,
            'tipo' => $tipo
        ]);
    }
}
