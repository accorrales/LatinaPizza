<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use App\Models\Tamano;
use App\Models\Masa;
use App\Models\Extra;
class DetallePedidoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'sabor_id' => 'required|exists:sabores,id',
            'tamano_id' => 'required|exists:tamanos,id',
            'masa_id' => 'required|exists:masas,id',
            'nota_cliente' => 'nullable|string|max:255',
            'extras' => 'nullable|array',
            'extras.*' => 'exists:extras,id',
        ]);

        // Obtener precios base
        $tamano = Tamano::findOrFail($validated['tamano_id']);
        $masa = Masa::findOrFail($validated['masa_id']);

        $precioTotal = (float) $tamano->precio_base + (float) $masa->precio_extra;

        // Calcular precio total de extras según el tamaño
        if (!empty($validated['extras'])) {
            $extras = Extra::whereIn('id', $validated['extras'])->get();

            foreach ($extras as $extra) {
                switch (strtolower($tamano->nombre)) {
                    case 'pequeña':
                        $precioTotal += (float) $extra->precio_pequena;
                        break;
                    case 'mediana':
                        $precioTotal += (float) $extra->precio_mediana;
                        break;
                    case 'grande':
                        $precioTotal += (float) $extra->precio_grande;
                        break;
                    case 'extragrande':
                        $precioTotal += (float) $extra->precio_extragrande;
                        break;
                }
            }
        }

        // Crear el detalle del pedido
        $detalle = DetallePedido::create([
            'pedido_id' => $validated['pedido_id'],
            'sabor_id' => $validated['sabor_id'],
            'tamano_id' => $validated['tamano_id'],
            'masa_id' => $validated['masa_id'],
            'nota_cliente' => $validated['nota_cliente'] ?? null,
            'precio_total' => $precioTotal,
        ]);

        // Adjuntar extras si hay
        if (!empty($validated['extras'])) {
            $extrasConPrecios = [];

            foreach ($validated['extras'] as $extraId) {
                $extra = Extra::find($extraId);

                // Obtenemos el precio correcto según el tamaño
                $precioExtra = match ((int)$validated['tamano_id']) {
                    1 => $extra->precio_pequena,
                    2 => $extra->precio_mediana,
                    3 => $extra->precio_grande,
                    4 => $extra->precio_extragrande,
                    default => 0
                };

                // Armamos el array para el attach
                $extrasConPrecios[$extraId] = ['precio_extra' => $precioExtra];
            }

            // Attach con valores personalizados
            $detalle->extras()->attach($extrasConPrecios);
        }

        return response()->json([
            'message' => 'Detalle de pedido creado correctamente',
            'detalle' => $detalle->load(['sabor', 'tamano', 'masa', 'extras'])
        ], 201);
    }
}