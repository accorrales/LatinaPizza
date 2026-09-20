<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetallePedidoPromocionController extends Controller
{
    public function detallesConPrecioYDesglose(Request $request, int $pedidoId): JsonResponse
    {
        $pedido = Pedido::with([
            'promociones.promocion',
            'promociones.sabor',
            'promociones.tamano',
            'promociones.masa',
            'promociones.producto',
            'promociones.extras',
        ])->findOrFail($pedidoId);

        $user = $request->user();
        abort_unless(
            $pedido->user_id === $user->id || $user->hasAnyRole('admin', 'cocina'),
            403,
            'No autorizado'
        );

        $snapshotPromotions = collect($pedido->detalle_json['items'] ?? [])
            ->where('tipo', 'promocion')
            ->values();

        $promotions = $snapshotPromotions->isNotEmpty()
            ? $snapshotPromotions->map(fn (array $item) => [
                'nombre' => $item['nombre'] ?? 'Promoción',
                'descripcion' => $item['descripcion'] ?? null,
                'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                'precio_total' => (float) ($item['precio_total'] ?? 0),
                'componentes' => collect($item['componentes'] ?? [])->map(fn (array $component) => [
                    'tipo' => $component['tipo'] ?? 'pizza',
                    'producto' => $component['producto'] ?? null,
                    'sabor' => $component['sabor'] ?? null,
                    'tamano' => $component['tamano'] ?? null,
                    'masa' => $component['masa'] ?? null,
                    'nota' => $component['nota_cliente'] ?? null,
                    'extras' => $component['extras'] ?? [],
                ])->values()->all(),
            ])->all()
            : $this->legacyPromotions($pedido);

        if ($promotions === []) {
            return response()->json(['message' => 'No se encontraron promociones para este pedido.'], 404);
        }

        return response()->json([
            'pedido_id' => $pedido->id,
            'subtotal' => (float) $pedido->subtotal,
            'delivery_fee' => (float) $pedido->delivery_fee,
            'total' => (float) $pedido->total,
            'promociones' => $promotions,
        ]);
    }

    private function legacyPromotions(Pedido $pedido): array
    {
        return $pedido->promociones
            ->groupBy('promocion_id')
            ->map(function ($details) {
                $promotion = $details->first()->promocion;

                return [
                    'nombre' => $promotion?->nombre ?? 'Promoción',
                    'descripcion' => $promotion?->descripcion,
                    'cantidad' => 1,
                    'precio_total' => (float) ($promotion?->precio_total ?? 0),
                    'componentes' => $details->map(fn ($detail) => [
                        'tipo' => $detail->producto_id ? 'bebida' : 'pizza',
                        'producto' => $detail->producto?->nombre,
                        'sabor' => $detail->sabor?->nombre,
                        'tamano' => $detail->tamano?->nombre,
                        'masa' => $detail->masa?->tipo,
                        'nota' => $detail->nota_cliente,
                        'extras' => $detail->extras->map(fn ($extra) => [
                            'nombre' => $extra->nombre,
                            'precio' => (float) $extra->pivot->precio_extra,
                        ])->values()->all(),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
