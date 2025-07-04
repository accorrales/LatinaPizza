<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetallePedidoPromocion;
use App\Models\Promocion;
use App\Models\Pedido;
use App\Models\Sabor;
use App\Models\Tamano;
use App\Models\Masa;
use App\Models\Extra;
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
            'productos.*.extras' => 'nullable|array',
            'productos.*.extras.*' => 'exists:extras,id',
        ]);

        $registros = [];

        foreach ($validated['productos'] as $producto) {
            $detalle = DetallePedidoPromocion::create([
                'pedido_id' => $validated['pedido_id'],
                'promocion_id' => $validated['promocion_id'],
                'sabor_id' => $producto['sabor_id'],
                'tamano_id' => $producto['tamano_id'],
                'masa_id' => $producto['masa_id'],
                'nota_cliente' => $producto['nota_cliente'] ?? null,
            ]);

            // Manejo de extras si vienen
            if (!empty($producto['extras'])) {
                $extrasConPrecios = [];

                $tamano = Tamano::find($producto['tamano_id']);

                foreach ($producto['extras'] as $extraId) {
                    $extra = Extra::find($extraId);

                    $precioExtra = match (strtolower($tamano->nombre)) {
                        'pequeña' => $extra->precio_pequena,
                        'mediana' => $extra->precio_mediana,
                        'grande' => $extra->precio_grande,
                        'extragrande' => $extra->precio_extragrande,
                        default => 0
                    };

                    $extrasConPrecios[$extraId] = ['precio_extra' => $precioExtra];
                }

                $detalle->extras()->attach($extrasConPrecios);
            }

            $registros[] = $detalle;
        }

        // Cargar relaciones incluyendo extras
        $registros = collect($registros);
        $registros->each->load(['sabor', 'tamano', 'masa', 'promocion', 'extras']);

        return response()->json([
            'message' => 'Promoción agregada correctamente al pedido',
            'detalles' => $registros
        ], 201);
    }
    public function detallesConPrecioYDesglose($pedido_id)
    {
        $detalles = DetallePedidoPromocion::with(['sabor', 'tamano', 'masa', 'promocion', 'extras'])
            ->where('pedido_id', $pedido_id)
            ->get();

        if ($detalles->isEmpty()) {
            return response()->json(['message' => 'No se encontraron detalles para este pedido'], 404);
        }

        $precioSinPromo = 0;
        $pizzas = [];

        foreach ($detalles as $detalle) {
            $tamano = $detalle->tamano;
            $masa = $detalle->masa;
            $precioBase = (float) $tamano->precio_base;
            $precioMasa = (float) $masa->precio_extra;

            $totalExtras = 0;
            $extrasDetalle = [];

            foreach ($detalle->extras as $extra) {
                $precioExtra = (float) $extra->pivot->precio_extra;
                $totalExtras += $precioExtra;

                $extrasDetalle[] = [
                    'nombre' => $extra->nombre,
                    'precio' => $precioExtra,
                ];
            }

            $subtotal = $precioBase + $precioMasa + $totalExtras;
            $precioSinPromo += $subtotal;

            $pizzas[] = [
                'sabor' => $detalle->sabor->nombre,
                'tamano' => $tamano->nombre,
                'precio_base' => $precioBase,
                'masa' => $masa->tipo,
                'precio_masa' => $precioMasa,
                'extras' => $extrasDetalle,
                'precio_total' => $subtotal,
                'nota' => $detalle->nota_cliente,
            ];
        }

        $promo = $detalles->first()->promocion;
        $precioPromo = (float) $promo->precio_total;
        $ahorro = $precioSinPromo - $precioPromo;

        return response()->json([
            'pedido_id' => $pedido_id,
            'promocion' => [
                'nombre' => $promo->nombre,
                'precio_total' => $precioPromo,
            ],
            'precio_sin_promocion' => $precioSinPromo,
            'ahorro_total' => $ahorro,
            'pizzas' => $pizzas,
        ]);
    }
}
