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

        // Calcular total
        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $total += $producto->precio * $item['cantidad'];
        }

        // Crear pedido
        $pedido = Pedido::create([
            'user_id' => Auth::id(),
            'sucursal_id' => Auth::user()->sucursal_id, // 👈 asociación automática
            'total' => $total,
            'estado' => 'pendiente',
            'tipo_pedido' => $request->tipo_pedido,
        ]);

        // Guardar historial inicial
        $pedido->guardarHistorial('pendiente');
        
        // Asociar productos
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

        // Transformamos cada pedido
        $pedidos->transform(function ($pedido) {
            if ($pedido->promociones && $pedido->promociones->count() > 0) {
                $pedido->tipo_contenido = 'promocion';

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

                // Nuevos campos con nombres claros
                $pedido->precio_sin_promocion = $precioSinPromo;
                $pedido->descuento = $descuento;
                $pedido->total_con_descuento = $totalConDescuento;
            } elseif ($pedido->detalles && $pedido->detalles->count() > 0) {
                $pedido->tipo_contenido = 'normal';
            } else {
                $pedido->tipo_contenido = 'productos';
            }

            return $pedido;
        });

        return response()->json($pedidos);
    }

    public function detallePedido($id, Request $request)
    {
        $pedido = Pedido::with('productos')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($pedido);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado,cancelado'
        ]);

        $pedido = Pedido::findOrFail($id);
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

        // Enviar notificación por correo
        Mail::to($pedido->usuario->email)->send(new EstadoPedidoMailable($pedido));

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'pedido' => $pedido
        ]);
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
        ])->where('user_id', $request->user()->id)
        ->findOrFail($id);

        return response()->json($pedido);
    }
}

