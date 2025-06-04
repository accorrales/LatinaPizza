<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoAdminController extends Controller
{
    public function index()
    {
        // Cargar pedidos con sus relaciones (usuario y productos)
        $pedidos = Pedido::with(['usuario', 'productos'])->orderByDesc('created_at')->get();

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

}

