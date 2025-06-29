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

        $pedidos = Pedido::with('productos')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

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
}

