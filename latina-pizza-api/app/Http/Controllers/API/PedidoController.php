<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function misPedidos(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $pedidos = Pedido::with([
            'productos',
            'detalles.sabor',
            'detalles.tamano',
            'detalles.masa',
            'detalles.extras',
            'promociones.promocion',
            'promociones.sabor',
            'promociones.tamano',
            'promociones.masa',
            'promociones.extras',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(min(max($request->integer('per_page', 20), 1), 50));

        $pedidos->getCollection()->transform(function ($pedido) {
            if ($pedido->promociones && $pedido->promociones->count() > 0) {
                $pedido->tipo_contenido = 'promocion';
            } elseif ($pedido->detalles && $pedido->detalles->count() > 0) {
                $pedido->tipo_contenido = 'normal';
            } else {
                $pedido->tipo_contenido = 'productos';
            }

            return $pedido;
        });

        return response()->json($pedidos);
    }

    public function show($id, Request $request)
    {
        $pedido = Pedido::with([
            'productos',
            'detalles.sabor',
            'detalles.tamano',
            'detalles.masa',
            'detalles.extras',
            'promociones.promocion',
            'usuario',
            'sucursal',
        ])
            ->findOrFail($id);

        // Seguridad: asegurarse que sea del usuario autenticado
        if ($pedido->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        return response()->json($pedido);
    }
}
