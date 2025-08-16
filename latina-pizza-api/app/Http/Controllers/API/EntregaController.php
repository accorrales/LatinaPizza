<?php

namespace App\Http\Controllers\API;

use App\Models\Carrito;
use App\Models\DireccionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class EntregaController extends Controller
{
    public function setMetodoEntrega(Request $r)
    {
        // Reglas: dirección requerida si es express; sucursal requerida si es pickup
        $data = $r->validate([
            'tipo'                 => 'required|in:pickup,express',
            'sucursal_id'          => 'nullable|integer|exists:sucursales,id',
            'direccion_usuario_id' => 'required_if:tipo,express|nullable|integer|exists:direcciones_usuario,id',
        ]);

        // Carrito del usuario
        $carrito = Carrito::firstOrCreate(['user_id' => Auth::id()]);

        if ($data['tipo'] === 'express') {
            // Seguridad: la dirección debe ser del usuario
            DireccionUsuario::where('user_id', Auth::id())
                ->findOrFail($data['direccion_usuario_id']);

            // Asignación explícita para evitar mass-assignment issues
            $carrito->tipo_entrega         = 'express';
            $carrito->direccion_usuario_id = $data['direccion_usuario_id'];

            // Si YA te mandan sucursal_id, la guardamos; si no, se queda null para elegir luego
            $carrito->sucursal_id = $data['sucursal_id'] ?? null;
            $carrito->save();

        } else { // pickup
            if (empty($data['sucursal_id'])) {
                return response()->json(['error' => 'Falta sucursal_id para pickup'], 422);
            }

            $carrito->tipo_entrega         = 'pickup';
            $carrito->sucursal_id          = $data['sucursal_id'];
            $carrito->direccion_usuario_id = null;
            $carrito->save();
        }

        return response()->json([
            'message' => 'Método de entrega actualizado',
            'data'    => $carrito->fresh(),
        ]);
    }
}
