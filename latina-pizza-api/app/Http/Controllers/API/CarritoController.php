<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoConfirmadoMail;
use App\Models\User;
class CarritoController extends Controller
{
    // Ver el carrito del usuario
    public function index()
    {
        $user = Auth::user();
        $carrito = $user->carrito()->with('productos')->first();

        return response()->json($carrito);
    }

    // Agregar producto al carrito
    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $carrito = $user->carrito()->firstOrCreate(['user_id' => $user->id]);

        // Agrega o actualiza el producto en el carrito
        $carrito->productos()->syncWithoutDetaching([
            $request->producto_id => ['cantidad' => $request->cantidad]
        ]);

        return response()->json(['message' => 'Producto agregado al carrito.']);
    }

    // Eliminar producto del carrito
    public function remove($productoId)
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        if ($carrito) {
            $carrito->productos()->detach($productoId);
            return response()->json(['message' => 'Producto eliminado del carrito.']);
        }

        return response()->json(['message' => 'No se encontró carrito.'], 404);
    }

    // Vaciar carrito
    public function clear()
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        if ($carrito) {
            $carrito->productos()->detach();
            return response()->json(['message' => 'Carrito vaciado.']);
        }

        return response()->json(['message' => 'No se encontró carrito.'], 404);
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $carrito = $user->carrito;
        $carrito->load('productos');

        if (!$carrito || $carrito->productos->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.'], 400);
        }

        try {
            DB::beginTransaction();

            $pedido = new Pedido();
            $pedido->user_id = $user->id;
            $pedido->estado = 'pendiente';
            $pedido->tipo_pedido = $request->input('tipo_pedido', 'express');
            $pedido->metodo_pago = $request->input('metodo_pago', 'efectivo');
            $pedido->sucursal_id = $user->sucursal_id;
            $pedido->save();

            foreach ($carrito->productos as $producto) {
                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $carrito->productos()->detach();

            DB::commit();

            // ✅ Enviar correo al usuario
            Mail::to($user->email)->send(new PedidoConfirmadoMail($pedido));

            return response()->json([
                'message' => '✅ Pedido creado correctamente',
                'pedido_id' => $pedido->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ Error al procesar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

