<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetallePedidoPromocion;
use App\Models\Promocion;
use App\Models\Pedido;

class DetallePedidoPromocionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'promocion_id' => 'required|exists:promociones,id',
            'productos' => 'required|array|min:1',
            'productos.*.sabor_id' => 'required|exists:sabores,id',
            'productos.*.tamano_id' => 'required|exists:tamanos,id',
            'productos.*.masa_id' => 'required|exists:masas,id',
            'productos.*.nota_cliente' => 'nullable|string|max:255',
        ]);

        $registros = [];

        foreach ($validated['productos'] as $producto) {
            $registros[] = DetallePedidoPromocion::create([
                'pedido_id' => $validated['pedido_id'],
                'promocion_id' => $validated['promocion_id'],
                'sabor_id' => $producto['sabor_id'],
                'tamano_id' => $producto['tamano_id'],
                'masa_id' => $producto['masa_id'],
                'nota_cliente' => $producto['nota_cliente'] ?? null,
            ]);
        }

        // Cargar relaciones correctamente
        $registros = collect($registros);
        $registros->each->load(['sabor', 'tamano', 'masa', 'promocion']);

        return response()->json([
            'message' => 'Promoción agregada correctamente al pedido',
            'detalles' => $registros
        ], 201);
    }
}
