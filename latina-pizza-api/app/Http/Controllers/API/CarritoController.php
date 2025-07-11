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
use App\Models\Masa;
use App\Models\Extra;
use Illuminate\Support\Facades\Log;
use App\Models\Tamano;
use App\Models\CarritoItem;
class CarritoController extends Controller
{
    // Ver el carrito del usuario
    public function index()
    {
        $user = Auth::user();
        $carrito = $user->carrito()->with([
            'items.producto.tamano',
            'items.producto.sabor',
            'items.masa',
            'items.extras'
        ])->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $items = [];
        $total = 0;

        foreach ($carrito->items as $item) {
            $producto = $item->producto;

            // Verificamos que el producto aún exista
            if (!$producto) continue;

            $subtotal = $item->precio_total;
            $total += $subtotal;

            $items[] = [
                'id' => $item->id,
                'nombre' => $producto->nombre,
                'tamano' => $producto->tamano->nombre ?? 'N/A',
                'sabor' => $producto->sabor->nombre ?? 'N/A',
                'masa_nombre' => $item->masa->tipo ?? 'N/A',
                'cantidad' => $item->cantidad,
                'nota_cliente' => $item->nota_cliente,
                'precio_total' => $item->precio_total,
                'extras' => $item->extras->map(fn($extra) => [
                    'id' => $extra->id,
                    'nombre' => $extra->nombre,
                ])->toArray(),
            ];
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }


    // Agregar producto al carrito
    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'masa_id' => 'nullable|exists:masas,id',
            'nota_cliente' => 'nullable|string',
            'extras' => 'array',
            'extras.*' => 'exists:extras,id',
        ]);

        $user = Auth::user();
        $carrito = $user->carrito()->firstOrCreate(['user_id' => $user->id]);

        $producto = Producto::with('tamano')->findOrFail($request->producto_id);
        $tamano = $producto->tamano;

        $precioProducto = $tamano->precio_base ?? 0;

        // Precio masa
        $precioMasa = 0;
        if ($request->filled('masa_id')) {
            $masa = Masa::find($request->masa_id);
            $precioMasa = $masa ? $masa->precio_extra : 0;
        }

        // Precio extras
        $precioExtras = 0;
        $extras = collect();
        if ($request->filled('extras')) {
            $extras = Extra::whereIn('id', $request->extras)->get();
            $tamanoNombre = strtolower($tamano->nombre);

            foreach ($extras as $extra) {
                if (str_contains($tamanoNombre, 'mediana')) {
                    $precioExtras += $extra->precio_mediana;
                } elseif (str_contains($tamanoNombre, 'grande') && !str_contains($tamanoNombre, 'extra')) {
                    $precioExtras += $extra->precio_grande;
                } elseif (str_contains($tamanoNombre, 'extra')) {
                    $precioExtras += $extra->precio_extragrande;
                } else {
                    $precioExtras += $extra->precio_pequena;
                }
            }
        }

        $precioTotal = ($precioProducto + $precioMasa + $precioExtras) * $request->cantidad;

        // Creamos nuevo ítem
        $item = new CarritoItem([
            'producto_id'   => $producto->id,
            'masa_id'       => $request->masa_id,
            'cantidad'      => $request->cantidad,
            'nota_cliente'  => $request->nota_cliente,
            'precio_total'  => $precioTotal,
        ]);

        $carrito->items()->save($item);

        if ($extras->isNotEmpty()) {
            $item->extras()->sync($extras->pluck('id')->toArray());
        }

        return response()->json(['message' => 'Producto personalizado agregado al carrito']);
    }

    // Eliminar producto del carrito
    public function remove($id)
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        // Verifica si el item pertenece al carrito del usuario
        $existe = DB::table('carrito_items')
            ->where('id', $id)
            ->where('carrito_id', $carrito->id)
            ->exists();

        if (!$existe) {
            return response()->json(['error' => 'No se pudo eliminar este producto.'], 404);
        }

        // Elimina los extras relacionados
        DB::table('carrito_item_extra')->where('carrito_item_id', $id)->delete();

        // Elimina el item del carrito
        DB::table('carrito_items')->where('id', $id)->delete();

        return response()->json(['success' => 'Producto eliminado correctamente.']);
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

