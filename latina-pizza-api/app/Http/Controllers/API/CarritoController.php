<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Masa;
use App\Models\Extra;
use App\Models\CarritoItem;
use App\Models\Promocion;
use App\Models\CarritoItemPromocionDetalle;
use App\Models\CarritoItemsPromocionExtra;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CarritoController extends Controller
{
    // Ver el carrito del usuario
    private function invalidateStripePI(Carrito $carrito): void
    {
        if (!Schema::hasColumn($carrito->getTable(), 'stripe_payment_intent_id')) {
            return;
        }

        $intentId = $carrito->stripe_payment_intent_id;
        $carrito->update(['stripe_payment_intent_id' => null]);

        if (!$intentId || !config('services.stripe.secret')) {
            return;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $intent = PaymentIntent::retrieve($intentId);
            if (!in_array($intent->status, ['succeeded', 'canceled'], true)) {
                $intent->cancel();
            }
        } catch (\Throwable $exception) {
            Log::warning('Could not cancel stale Stripe intent.', [
                'intent_id' => $intentId,
                'exception' => $exception::class,
            ]);
        }
    }
    public function index()
    {
        $user = Auth::user();

        /** @var Carrito $carrito */
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

        // Cargamos todo lo que el front necesita mostrar
        $carrito->load([
            'items.producto.tamano',
            'items.producto.sabor',
            'items.masa',
            'items.extras',
            'items.promocion',
            'items.detallesPromocion.sabor',
            'items.detallesPromocion.tamano',
            'items.detallesPromocion.masa',
            'items.detallesPromocion.producto',
            'items.detallesPromocion.extras.extra',
        ]);

        // Si no hay items, responde estructura completa con totales en 0
        if ($carrito->items->isEmpty()) {
            return response()->json([
                'data' => [
                    'id'                   => $carrito->id,
                    'tipo_entrega'         => $carrito->tipo_entrega,
                    'sucursal_id'          => $carrito->sucursal_id,
                    'direccion_usuario_id' => $carrito->direccion_usuario_id,
                    'delivery_fee'         => (float) ($carrito->delivery_fee ?? 0),
                    'delivery_distance_km' => (float) ($carrito->delivery_distance_km ?? 0),
                    'delivery_currency'    => $carrito->delivery_currency,
                    'items'                => [],
                ],
                'subtotal' => 0.0,
                'delivery' => [
                    'fee'      => (float) ($carrito->delivery_fee ?? 0),
                    'currency' => $carrito->delivery_currency,
                    'distance' => (float) ($carrito->delivery_distance_km ?? 0),
                ],
                'total' => 0.0,
            ]);
        }

        // Construimos la misma estructura que ya usabas, pero además devolvemos totales
        $items = [];
        $subtotal = 0.0;

        foreach ($carrito->items as $item) {
            // PRODUCTO normal
            if ($item->producto_id && $item->producto) {
                $subtotal += (float) $item->precio_total;

                $items[] = [
                    'id'           => $item->id,
                    'tipo'         => 'producto',
                    'nombre'       => $item->producto->nombre,
                    'tamano'       => $item->producto->tamano->nombre ?? 'N/A',
                    'sabor'        => $item->producto->sabor->nombre ?? 'N/A',
                    'masa_nombre'  => $item->masa->tipo ?? 'N/A',
                    'cantidad'     => (int) $item->cantidad,
                    'nota_cliente' => $item->nota_cliente,
                    'precio_total' => (float) $item->precio_total,
                    'extras'       => $item->extras->map(fn ($extra) => [
                        'id'     => $extra->id,
                        'nombre' => $extra->nombre,
                    ])->values(),
                ];
            }

            // PROMOCIÓN personalizada
            elseif ($item->promocion_id && $item->promocion) {
                $precioBD    = (float) $item->precio_total;
                $extrasTotal = 0.0;

                $componentes = $item->detallesPromocion->map(function ($detalle) use (&$extrasTotal) {
                    if ($detalle->tipo === 'pizza') {
                        $tamanoNombre = strtolower($detalle->tamano->nombre ?? 'mediana');

                        $extras = $detalle->extras->map(function ($e) use (&$extrasTotal, $tamanoNombre) {
                            $precio = match ($tamanoNombre) {
                                'pequena', 'pequeña'   => (float) ($e->extra->precio_pequena     ?? 0),
                                'mediana'             => (float) ($e->extra->precio_mediana     ?? 0),
                                'grande'              => (float) ($e->extra->precio_grande      ?? 0),
                                'extragrande', 'extra grande'
                                                     => (float) ($e->extra->precio_extragrande ?? 0),
                                default               => (float) ($e->extra->precio_mediana     ?? 0),
                            };

                            $extrasTotal += $precio;

                            return [
                                'id'     => $e->extra->id,
                                'nombre' => $e->extra->nombre,
                                'precio' => $precio,
                            ];
                        });

                        return [
                            'tipo'         => 'pizza',
                            'sabor'        => ['nombre' => $detalle->sabor->nombre ?? 'N/A'],
                            'masa'         => ['nombre' => $detalle->masa->tipo   ?? 'N/A'],
                            'tamano'       => ['nombre' => ucfirst($tamanoNombre)],
                            'nota_cliente' => $detalle->nota_cliente,
                            'extras'       => $extras->values(),
                        ];
                    }

                    if ($detalle->tipo === 'bebida') {
                        return [
                            'tipo'     => 'bebida',
                            'producto' => ['nombre' => $detalle->producto->nombre ?? 'N/A'],
                        ];
                    }

                    return ['tipo' => 'desconocido'];
                })->values();

                $subtotal += $precioBD;

                $items[] = [
                    'id'            => $item->id,
                    'tipo'          => 'promocion',
                    'nombre'        => $item->promocion->nombre,
                    'descripcion'   => $item->promocion->descripcion,
                    'imagen'        => $item->promocion->imagen ?? null,
                    'pizzas'        => $componentes,
                    'precio_total'  => $precioBD,
                    'cantidad'      => (int) ($item->cantidad ?: 1),
                    'desglose'      => [
                        'base'   => max(0, $precioBD - $extrasTotal),
                        'extras' => $extrasTotal,
                    ],
                ];
            }
        }

        $deliveryFee = ($carrito->tipo_entrega === 'express')
            ? (float) ($carrito->delivery_fee ?? 0)
            : 0.0;

        // 👇 ESTA ES LA CLAVE: usa el mismo cálculo que checkout (suma base + extras de promos)
        $subtotalOk = round($carrito->calcSubtotal(), 2);
        $totalOk    = round($subtotalOk + $deliveryFee, 2);

        return response()->json([
            'data' => [
                'id' => $carrito->id,
                'tipo_entrega' => $carrito->tipo_entrega,
                'sucursal_id' => $carrito->sucursal_id,
                'direccion_usuario_id' => $carrito->direccion_usuario_id,
                'items' => $items,
            ],
            'subtotal' => $subtotalOk,         // 👈 ya incluye extras de promo
            'delivery' => [
                'fee'      => $deliveryFee,
                'currency' => $carrito->delivery_currency,
                'distance' => (float) ($carrito->delivery_distance_km ?? 0),
            ],
            'total' => $totalOk,               // 👈 ya correcto
        ]);
    }

    // Agregar producto al carrito
    public function add(Request $request)
    {
        $request->validate([
            'producto_id'  => 'required|exists:productos,id',
            'cantidad'     => 'required|integer|min:1|max:50',
            'masa_id'      => 'nullable|exists:masas,id',
            'nota_cliente' => 'nullable|string|max:500',
            'extras'       => 'array|max:20',
            'extras.*'     => 'distinct|exists:extras,id',
        ]);

        $user    = Auth::user();
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

        $producto = Producto::with('tamano')
            ->where('estado', true)
            ->findOrFail($request->producto_id);
        $tamano   = $producto->tamano;
        if (!$tamano) {
            return response()->json(['message' => 'El producto no tiene un tamaño válido.'], 422);
        }
        $precioProducto = (float) ($tamano->precio_base ?? 0);

        $precioMasa = 0.0;
        if ($request->filled('masa_id')) {
            $masa = Masa::find($request->masa_id);
            $precioMasa = (float) ($masa->precio_extra ?? 0);
        }

        $precioExtras = 0.0;
        $extras = collect();
        if ($request->filled('extras')) {
            $extras = Extra::whereIn('id', $request->extras)->get();
            $tn = strtolower($tamano->nombre ?? '');

            foreach ($extras as $extra) {
                $precioExtras += match (true) {
                    str_contains($tn, 'extra')  => (float) ($extra->precio_extragrande ?? 0),
                    str_contains($tn, 'grande') => (float) ($extra->precio_grande      ?? 0),
                    str_contains($tn, 'mediana')=> (float) ($extra->precio_mediana     ?? 0),
                    default                     => (float) ($extra->precio_pequena     ?? 0),
                };
            }
        }

        $precioTotal = ($precioProducto + $precioMasa + $precioExtras) * (int) $request->cantidad;

        $item = new CarritoItem([
            'producto_id'  => $producto->id,
            'masa_id'      => $request->masa_id,
            'cantidad'     => (int) $request->cantidad,
            'nota_cliente' => $request->nota_cliente,
            'precio_total' => $precioTotal,
        ]);
        $carrito->items()->save($item);

        if ($extras->isNotEmpty()) {
            $item->extras()->sync($extras->pluck('id')->all());
        }

        // 👇 invalida el intent porque el carrito cambió
        $this->invalidateStripePI($carrito);

        return response()->json(['message' => 'Producto agregado al carrito']);
    }

    public function updateQuantity(Request $request, $id)
    {
        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $carrito = Carrito::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $carrito->items()->with(['producto.tamano', 'masa', 'extras'])->findOrFail($id);

        if (!$item->producto_id || !$item->producto || !$item->producto->estado) {
            return response()->json(['message' => 'Este elemento no permite modificar su cantidad.'], 422);
        }

        $unitPrice = (float) $item->producto->tamano->precio_base
            + (float) ($item->masa->precio_extra ?? 0);
        foreach ($item->extras as $extra) {
            $unitPrice += $this->extraPriceForSize($extra, $item->producto->tamano->nombre);
        }

        $item->update([
            'cantidad' => $validated['cantidad'],
            'precio_total' => round($unitPrice * $validated['cantidad'], 2),
        ]);
        $this->invalidateStripePI($carrito);

        return response()->json(['message' => 'Cantidad actualizada.', 'item' => $item->fresh()]);
    }

    public function agregarPromocion(Request $request)
    {
        $validated = $request->validate([
            'promocion_id' => ['required', 'integer', 'exists:promociones,id'],
            'productos' => ['required', 'array', 'min:1', 'max:20'],
            'productos.*.tipo' => ['required', 'in:pizza,bebida'],
            'productos.*.sabor_id' => ['nullable', 'integer', 'exists:sabores,id'],
            'productos.*.masa_id' => ['nullable', 'integer', 'exists:masas,id'],
            'productos.*.producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'productos.*.extras' => ['nullable', 'array', 'max:20'],
            'productos.*.extras.*' => ['integer', 'distinct', 'exists:extras,id'],
            'productos.*.nota_cliente' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);
        $promocion = Promocion::with(['componentes.tamano'])->findOrFail($validated['promocion_id']);

        $pizzaRules = $promocion->componentes
            ->where('tipo', 'pizza')
            ->flatMap(fn ($component) => collect(range(1, max(1, (int) $component->cantidad)))
                ->map(fn () => $component))
            ->values();
        $expectedDrinks = $promocion->componentes
            ->where('tipo', 'bebida')
            ->sum(fn ($component) => max(1, (int) $component->cantidad));

        $pizzas = collect($validated['productos'])->where('tipo', 'pizza')->values();
        $drinks = collect($validated['productos'])->where('tipo', 'bebida')->values();

        if ($pizzas->count() !== $pizzaRules->count() || $drinks->count() !== $expectedDrinks) {
            return response()->json(['message' => 'Los componentes seleccionados no coinciden con la promoción.'], 422);
        }
        if ($pizzaRules->contains(fn ($component) => !$component->tamano)) {
            return response()->json(['message' => 'La promoción tiene un tamaño sin configurar.'], 422);
        }

        foreach ($pizzas as $pizza) {
            if (empty($pizza['sabor_id']) || empty($pizza['masa_id'])) {
                return response()->json(['message' => 'Cada pizza requiere sabor y masa.'], 422);
            }
        }
        foreach ($drinks as $drink) {
            if (empty($drink['producto_id'])) {
                return response()->json(['message' => 'Debe seleccionar la bebida incluida.'], 422);
            }
        }
        $drinkIds = $drinks->pluck('producto_id')->unique()->values();
        $validDrinkCount = Producto::whereIn('id', $drinkIds)
            ->where('estado', true)
            ->whereHas('categoria', fn ($query) => $query->whereRaw(
                'LOWER(nombre) IN (?, ?, ?)',
                ['bebidas', 'bebida', 'refrescos']
            ))
            ->count();
        if ($validDrinkCount !== $drinkIds->count()) {
            return response()->json(['message' => 'La bebida seleccionada no está disponible.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($carrito, $promocion, $pizzas, $pizzaRules, $drinks) {
                $item = $carrito->items()->create([
                    'promocion_id' => $promocion->id,
                    'cantidad' => 1,
                    'precio_total' => 0,
                ]);

                $extrasTotal = 0.0;
                foreach ($pizzas as $index => $pizza) {
                    $rule = $pizzaRules[$index];
                    $tamano = $rule->tamano;

                    $detalle = $item->detallesPromocion()->create([
                        'tipo' => 'pizza',
                        'sabor_id' => $pizza['sabor_id'],
                        'tamano_id' => $tamano->id,
                        'masa_id' => $pizza['masa_id'],
                        'nota_cliente' => $pizza['nota_cliente'] ?? null,
                    ]);

                    $extras = Extra::whereIn('id', $pizza['extras'] ?? [])->get();
                    foreach ($extras as $extra) {
                        $precio = $this->extraPriceForSize($extra, $tamano->nombre);
                        $extrasTotal += $precio;
                        $detalle->extras()->create([
                            'extra_id' => $extra->id,
                            'precio' => $precio,
                        ]);
                    }
                }

                foreach ($drinks as $drink) {
                    $item->detallesPromocion()->create([
                        'tipo' => 'bebida',
                        'producto_id' => $drink['producto_id'],
                    ]);
                }

                $total = round((float) $promocion->precio_total + $extrasTotal, 2);
                $item->update(['precio_total' => $total]);
                $this->invalidateStripePI($carrito);

                return ['item' => $item, 'total' => $total, 'extras' => $extrasTotal];
            });

            return response()->json([
                'message' => 'Promoción agregada correctamente al carrito.',
                'carrito_item_id' => $result['item']->id,
                'precio_total' => $result['total'],
                'desglose' => [
                    'base' => (float) $promocion->precio_total,
                    'extras' => $result['extras'],
                ],
            ], 201);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'No se pudo agregar la promoción.'], 500);
        }
    }


    // Eliminar producto del carrito
    public function remove($id)
    {
        $user = Auth::user();
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

        $item = CarritoItem::where('carrito_id', $carrito->id)->find($id);
        if (!$item) {
            return response()->json(['error' => 'No se pudo eliminar este producto.'], 404);
        }

        $item->detallesPromocion()->each(function ($det) {
            CarritoItemsPromocionExtra::where('detalle_id', $det->id)->delete();
            $det->delete();
        });

        $item->extras()->detach();
        $item->delete();

        // 👇 invalida el intent porque el carrito cambió
        $this->invalidateStripePI($carrito);

        return response()->json(['success' => 'Producto eliminado correctamente.']);
    }


    // Vaciar carrito
    public function clear()
    {
        $user = Auth::user();
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

        $itemsIds = $carrito->items()->pluck('id');

        $detallesIds = CarritoItemPromocionDetalle::whereIn('carrito_item_id', $itemsIds)->pluck('id');
        CarritoItemsPromocionExtra::whereIn('detalle_id', $detallesIds)->delete();
        CarritoItemPromocionDetalle::whereIn('id', $detallesIds)->delete();

        DB::table('carrito_item_extra')->whereIn('carrito_item_id', $itemsIds)->delete();

        CarritoItem::whereIn('id', $itemsIds)->delete();

        // 👇 invalida el intent porque el carrito cambió
        $this->invalidateStripePI($carrito);

        return response()->json(['message' => 'Carrito vaciado.']);
    }

    private function extraPriceForSize(Extra $extra, string $sizeName): float
    {
        $normalized = mb_strtolower($sizeName);

        return match (true) {
            str_contains($normalized, 'extra') => (float) ($extra->precio_extragrande ?? 0),
            str_contains($normalized, 'grande') => (float) ($extra->precio_grande ?? 0),
            str_contains($normalized, 'mediana') => (float) ($extra->precio_mediana ?? 0),
            default => (float) ($extra->precio_pequena ?? 0),
        };
    }

}
