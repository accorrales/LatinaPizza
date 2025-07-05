<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\EstadoPedidoMailable;
use App\Mail\PedidoConfirmadoMail;
use App\Models\Carrito;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoProducto;
use App\Models\PedidoHistorial;
use App\Models\Sucursal;
use App\Models\DetallePedido;
use App\Models\DetallePedidoExtra;
class PedidoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tipo_pedido' => 'required|in:para llevar,express',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1'
        ]);

        $total = 0;

        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $total += $producto->precio * $item['cantidad'];
        }

        $pedido = Pedido::create([
            'user_id' => Auth::id(),
            'sucursal_id' => Auth::user()->sucursal_id,
            'total' => $total,
            'estado' => 'pendiente',
            'tipo_pedido' => $request->tipo_pedido,
        ]);

        $pedido->guardarHistorial('pendiente');

        foreach ($request->productos as $item) {
            $pedido->productos()->attach($item['id'], ['cantidad' => $item['cantidad']]);
        }

        return response()->json([
            'message' => 'Pedido creado exitosamente',
            'pedido' => $pedido->load('productos')
        ]);
    }

    public function index()
    {
        $pedidos = Pedido::with('productos')->where('user_id', Auth::id())->get();

        return response()->json($pedidos);
    }

    public function misPedidos(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $pedidos = Pedido::with([
            'productos',
            'detalles.sabor',
            'detalles.tamano',
            'detalles.masa',
            'detalles.extras',
            'promociones.promocion',
            'promociones.tamano',
            'promociones.masa',
            'promociones.extras',
        ])
        ->where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->get();

        $pedidos->transform(function ($pedido) {
            if ($pedido->promociones && $pedido->promociones->count() > 0) {
                $datos = $this->calcularDesglosePromocion($pedido);
                foreach ($datos as $key => $value) {
                    $pedido->$key = $value;
                }
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado,cancelado'
        ]);

        $pedido = Pedido::findOrFail($id);

        // Seguridad extra (por si el usuario no es admin)
        if ($pedido->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'pedido' => $pedido
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado,cancelado'
        ]);

        $pedido = Pedido::with('usuario')->findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();

        Mail::to($pedido->usuario->email)->send(new EstadoPedidoMailable($pedido));

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'pedido' => $pedido
        ]);
    }

    // 🔒 Ya no necesitas detallePedido() separado porque show() hace todo

    // 👉 Método limpio para calcular desgloses
    private function calcularDesglosePromocion($pedido)
    {
        $precioSinPromo = 0;
        foreach ($pedido->promociones as $promocion) {
            $pizza = $promocion->sabor->precio ?? 0;
            $tamano = $promocion->tamano->precio ?? 0;
            $masa = $promocion->masa->precio ?? 0;
            $extras = $promocion->extras->sum('precio');

            $precioSinPromo += $pizza + $tamano + $masa + $extras;
        }

        $descuento = $pedido->promociones->sum(fn($p) => $p->promocion->precio_total ?? 0);
        $totalConDescuento = $precioSinPromo - $descuento;

        return [
            'tipo_contenido' => 'promocion',
            'precio_sin_promocion' => $precioSinPromo,
            'descuento' => $descuento,
            'total_con_descuento' => $totalConDescuento
        ];
    }
}


