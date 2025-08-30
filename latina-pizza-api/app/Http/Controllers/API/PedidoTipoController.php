<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Carrito;
use App\Models\Pedido;
class PedidoTipoController extends Controller
{
    public function guardar(Request $request)
    {
        $tipo = $request->input('tipo'); // 'pickup' | 'express' | null

        // Si viene null, limpiamos selección
        if ($tipo === null) {
            session()->forget('tipo_pedido');

            // Si está logueado, también limpiamos en BD (opcional)
            if (Auth::check()) {
                $carrito = Auth::user()->carrito()->first();
                if ($carrito) {
                    $carrito->update([
                        'tipo_entrega'         => null,
                        'delivery_fee'         => 0,
                        'delivery_distance_km' => 0,
                    ]);
                }
            }

            return response()->json(['ok' => true, 'tipo' => null]);
        }

        // Validación
        if (!in_array($tipo, ['pickup', 'express'], true)) {
            return response()->json(['error' => 'Tipo de pedido inválido'], 422);
        }

        // Siempre guardamos en sesión para que el modal sepa la última elección
        session(['tipo_pedido' => $tipo]);

        // Si está logueado: persistimos en BD para que checkout y totales estén alineados
        $persisted = false;
        $carritoId = null;

        if (Auth::check()) {
            $carrito = Auth::user()->carrito()->firstOrCreate([]);

            $payload = ['tipo_entrega' => $tipo];

            // Si cambia a pickup, resetea delivery del carrito
            if ($tipo === 'pickup') {
                $payload['delivery_fee'] = 0;
                $payload['delivery_distance_km'] = 0;
            }

            $carrito->update($payload);
            $persisted = true;
            $carritoId = $carrito->id;
        }

        return response()->json([
            'ok'               => true,
            'tipo'             => $tipo,
            'persisted_to_db'  => $persisted,
            'carrito_id'       => $carritoId,
            'is_authenticated' => Auth::check(),
        ]);
    }
}
