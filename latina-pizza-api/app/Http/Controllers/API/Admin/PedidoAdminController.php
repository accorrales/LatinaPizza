<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // ya viene autenticado

        $pedidos = Pedido::with('productos', 'usuario')
            ->where('sucursal_id', $user->sucursal_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pedidos);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado,cancelado'
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $request->estado;
        $pedido->save();
        $pedido->guardarHistorial($request->estado);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'pedido' => $pedido
        ]);
    }
    public function filtrar(Request $request)
    {
        $estado = $request->query('estado');
        $tipo = $request->query('tipo_pedido');

        $query = Pedido::with('productos', 'usuario');

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo) {
            $query->where('tipo_pedido', $tipo);
        }

        $resultados = $query->orderBy('created_at', 'desc')->get();

        return response()->json($resultados);
    }
    public function tiempoEstimado()
    {
        // Configuraciones por tipo
        $config = [
            'para llevar' => ['base' => 10, 'por_pedido' => 5],
            'express' => ['base' => 20, 'por_pedido' => 7],
        ];

        $estimados = [];

        foreach ($config as $tipo => $tiempo) {
            $pendientes = Pedido::where('estado', 'pendiente')
                ->where('tipo_pedido', $tipo)
                ->count();

            $total = $tiempo['base'] + ($pendientes * $tiempo['por_pedido']);

            $estimados[str_replace(' ', '_', $tipo)] = $total . ' minutos';
        }

        return response()->json($estimados);
    }
    public function verHistorial($id)
    {
        $pedido = Pedido::findOrFail($id);

        $historial = $pedido->historial()->orderByDesc('fecha')->get();

        return response()->json($historial);
    }
    public function resumenSucursal($id)
    {
        // Obtener pedidos de la sucursal
        $pedidos = Pedido::with('productos')
            ->where('sucursal_id', $id)
            ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos para esta sucursal'], 404);
        }

        // Estadísticas
        $total = $pedidos->count();
        $pendientes = $pedidos->where('estado', 'pendiente')->count();
        $preparando = $pedidos->where('estado', 'preparando')->count();
        $entregados = $pedidos->where('estado', 'entregado')->count();
        $cancelados = $pedidos->where('estado', 'cancelado')->count();

        // Top productos (agrupando por ID y sumando cantidades)
        $topProductos = collect();
        foreach ($pedidos as $pedido) {
            foreach ($pedido->productos as $producto) {
                $existente = $topProductos->firstWhere('id', $producto->id);
                if ($existente) {
                    $existente->total += $producto->pivot->cantidad;
                } else {
                    $topProductos->push((object)[
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'total' => $producto->pivot->cantidad
                    ]);
                }
            }
        }

        // Top 5
        $topProductos = $topProductos->sortByDesc('total')->values()->take(5);

        $totalGanancias = Pedido::where('sucursal_id', $id)
        ->whereIn('estado', ['pendiente', 'preparando', 'entregado']) // ajustable
        ->sum('total');

        return response()->json([
            'sucursal_id' => $id,
            'resumen' => [
                'total_pedidos' => $pedidos->count(),
                'total_ganancias' => $totalGanancias,
                'pendientes' => $pedidos->where('estado', 'pendiente')->count(),
                'preparando' => $pedidos->where('estado', 'preparando')->count(),
                'entregados' => $pedidos->where('estado', 'entregado')->count(),
                'cancelados' => $pedidos->where('estado', 'cancelado')->count(),
                'top_productos' => $topProductos,
            ],
        ]);
    }
}

