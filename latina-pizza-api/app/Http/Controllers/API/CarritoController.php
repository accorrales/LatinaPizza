<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoConfirmadoMail;
use App\Models\User;
use App\Models\Masa;
use App\Models\Extra;
use Illuminate\Support\Facades\Log;
use App\Models\Tamano;
use App\Models\CarritoItem;
use App\Models\CarritoItemExtra;
use App\Models\Promocion;
use App\Models\CarritoItemPromocionDetalle;
use App\Models\CarritoItemsPromocionExtra;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Mail\FacturaPedidoMail;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Support\Money;

class CarritoController extends Controller
{
    // Ver el carrito del usuario
    private function invalidateStripePI(Carrito $carrito): void
    {
        if (\Schema::hasColumn($carrito->getTable(), 'stripe_payment_intent_id')) {
            $carrito->update(['stripe_payment_intent_id' => null]);
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
            'items.detallesPromocion.masa',
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
                // ...
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
            'cantidad'     => 'required|integer|min:1',
            'masa_id'      => 'nullable|exists:masas,id',
            'nota_cliente' => 'nullable|string',
            'extras'       => 'array',
            'extras.*'     => 'exists:extras,id',
        ]);

        $user    = Auth::user();
        $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

        $producto = Producto::with('tamano')->findOrFail($request->producto_id);
        $tamano   = $producto->tamano;
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

    public function agregarPromocion(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $carrito = Carrito::firstOrCreate(['user_id' => $user->id]);

            $promocion  = Promocion::with('componentes')->findOrFail($request->promocion_id);
            $precioBase = (float) $promocion->precio_total;
            $precioExtras = 0.0;
            $detalles = [];

            foreach ($request->productos as $producto) {
                if ($producto['tipo'] === 'pizza') {
                    $detalle = new CarritoItemPromocionDetalle([
                        'tipo'         => 'pizza',
                        'sabor_id'     => $producto['sabor_id'],
                        'masa_id'      => $producto['masa_id'],
                        'nota_cliente' => $producto['nota_cliente'] ?? null,
                    ]);
                    $detalle->tamano = strtolower($producto['tamano'] ?? 'mediana');
                    $detalles[] = ['detalle' => $detalle, 'extras' => $producto['extras'] ?? []];
                } elseif ($producto['tipo'] === 'bebida') {
                    $detalle = new CarritoItemPromocionDetalle([
                        'tipo'        => 'bebida',
                        'producto_id' => $producto['producto_id'],
                    ]);
                    $detalles[] = ['detalle' => $detalle, 'extras' => []];
                }
            }

            $item = CarritoItem::create([
                'carrito_id'   => $carrito->id,
                'promocion_id' => $promocion->id,
                'cantidad'     => 1,
                'precio_total' => 0,
            ]);

            foreach ($detalles as $data) {
                $detalle = $data['detalle'];
                $tam     = $detalle->tamano ?? 'mediana';
                unset($detalle->tamano);

                $detalle->carrito_item_id = $item->id;
                $detalle->save();

                foreach ($data['extras'] as $extraId) {
                    $extra = Extra::find($extraId);
                    if (!$extra) continue;

                    $precioExtra = match (strtolower($tam)) {
                        'pequena', 'pequeña' => (float) ($extra->precio_pequena     ?? 0),
                        'grande'             => (float) ($extra->precio_grande      ?? 0),
                        'extragrande'        => (float) ($extra->precio_extragrande ?? 0),
                        default              => (float) ($extra->precio_mediana     ?? 0),
                    };

                    $precioExtras += $precioExtra;

                    CarritoItemsPromocionExtra::create([
                        'detalle_id' => $detalle->id,
                        'extra_id'   => $extra->id,
                        'precio'     => $precioExtra,
                    ]);
                }
            }

            $precioTotal = $precioBase + $precioExtras;
            $item->update(['precio_total' => $precioTotal]);

            // 👇 invalida el intent porque el carrito cambió
            $this->invalidateStripePI($carrito);

            DB::commit();

            return response()->json([
                'message'         => '✅ Promoción agregada correctamente al carrito',
                'carrito_item_id' => $item->id,
                'precio_total'    => $precioTotal,
                'desglose'        => [
                    'base'   => $precioBase,
                    'extras' => $precioExtras,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => '❌ No se pudo agregar la promoción',
                'debug' => $e->getMessage(),
            ], 500);
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

    public function checkout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        /** @var \App\Models\Carrito $carrito */
        $carrito = $user->carrito()->with([
            'items.producto.tamano',
            'items.producto.sabor',
            'items.masa',
            'items.extras',
            'items.promocion',
            'items.detallesPromocion.sabor',
            'items.detallesPromocion.masa',
            'items.detallesPromocion.extras.extra',
        ])->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.'], 400);
        }

        // === Totales (como los tenías) ===
        $subtotal    = round($carrito->calcSubtotal(), 2);
        $deliveryFee = ($carrito->tipo_entrega === 'express') ? (float) ($carrito->delivery_fee ?? 0) : 0.0;
        $total       = round($subtotal + $deliveryFee, 2);

        // === DEBUG: breakdown exacto desde BD para detectar desajustes de promociones/extras ===
        if (method_exists($carrito, 'subtotalBreakdown')) {
            $bd        = $carrito->subtotalBreakdown();
            $totalCalc = round(($bd['subtotal'] ?? 0) + $deliveryFee, 2);

            \Illuminate\Support\Facades\Log::debug('CHECKOUT_BREAKDOWN', [
                'user_id'         => $user->id,
                'carrito_id'      => $carrito->id,
                'sum_items_raw'   => $bd['sum_items_raw']   ?? null,
                'sum_items_x_qty' => $bd['sum_items_x_qty'] ?? null,
                'sum_extras'      => $bd['sum_extras']      ?? null,
                'subtotal_bd'     => $bd['subtotal']        ?? null,
                'delivery'        => $deliveryFee,
                'total_calc'      => $totalCalc,
                'subtotal_view'   => $subtotal,
                'total_view'      => $total,
                'items'           => $bd['items']  ?? [],
                'extras'          => $bd['extras'] ?? [],
            ]);
        }

        // === Validar método de pago ===
        $metodo = $request->input('metodo_pago', 'efectivo');
        if (!in_array($metodo, ['efectivo', 'datafono', 'stripe'], true)) {
            return response()->json(['message' => 'Método de pago inválido'], 422);
        }

        if ($metodo === 'stripe') {
            $pi = $request->input('payment_intent_id');
            if (!$pi) {
                return response()->json(['message' => 'Falta payment_intent_id para Stripe'], 422);
            }

            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $intent = \Stripe\PaymentIntent::retrieve($pi);
            } catch (\Throwable $e) {
                return response()->json(['message' => 'PaymentIntent inválido'], 422);
            }

            // Estados aceptables
            if ($intent->status !== 'succeeded') {
                return response()->json(['message' => 'El pago no está confirmado'], 402);
            }

            // (Opcional pero recomendado) validar monto/moneda (tu lógica original)
            $expectedAmount   = (int) round($total * 100);
            $expectedCurrency = config('services.stripe.currency', 'crc');

            if ((int)$intent->amount !== $expectedAmount || $intent->currency !== $expectedCurrency) {
                \Illuminate\Support\Facades\Log::warning('CHECKOUT_MISMATCH_AMOUNT', [
                    'pi_id'     => $intent->id,
                    'pi_amount' => $intent->amount,
                    'pi_curr'   => $intent->currency,
                    'expected'  => $expectedAmount,
                    'exp_curr'  => $expectedCurrency,
                    'subtotal'  => $subtotal,
                    'delivery'  => $deliveryFee,
                    'total'     => $total,
                ]);
                return response()->json(['message' => 'Monto o moneda no coinciden'], 422);
            }
        }

        // ===== Snapshot de items (igual que ya lo tenías) =====
        $itemsPayload = [];
        foreach ($carrito->items as $item) {
            if ($item->producto_id && $item->producto) {
                $itemsPayload[] = [
                    'tipo'          => 'producto',
                    'producto_id'   => $item->producto_id,
                    'nombre'        => $item->producto->nombre,
                    'tamano'        => $item->producto->tamano->nombre ?? 'N/A',
                    'sabor'         => $item->producto->sabor->nombre ?? 'N/A',
                    'masa_nombre'   => $item->masa->tipo ?? 'N/A',
                    'cantidad'      => (int) $item->cantidad,
                    'nota_cliente'  => $item->nota_cliente,
                    'precio_total'  => (float) $item->precio_total,
                    'extras'        => $item->extras->map(fn($e)=>[
                        'id'=>$e->id,'nombre'=>$e->nombre
                    ])->values(),
                ];
            } elseif ($item->promocion_id && $item->promocion) {
                $extrasTotal = 0.0;
                $componentes = $item->detallesPromocion->map(function ($d) use (&$extrasTotal) {
                    if ($d->tipo === 'pizza') {
                        $tamanoNombre = strtolower($d->tamano->nombre ?? 'mediana');
                        $extras = $d->extras->map(function ($e) use (&$extrasTotal, $tamanoNombre) {
                            $precio = match ($tamanoNombre) {
                                'pequena','pequeña'     => (float) ($e->extra->precio_pequena     ?? 0),
                                'grande'                => (float) ($e->extra->precio_grande      ?? 0),
                                'extragrande','extra grande'
                                                        => (float) ($e->extra->precio_extragrande ?? 0),
                                default                 => (float) ($e->extra->precio_mediana     ?? 0),
                            };
                            $extrasTotal += $precio;
                            return ['id'=>$e->extra->id,'nombre'=>$e->extra->nombre,'precio'=>$precio];
                        });

                        return [
                            'tipo'         => 'pizza',
                            'sabor'        => ['nombre'=>$d->sabor->nombre ?? 'N/A'],
                            'masa'         => ['nombre'=>$d->masa->tipo   ?? 'N/A'],
                            'tamano'       => ['nombre'=>ucfirst($tamanoNombre)],
                            'nota_cliente' => $d->nota_cliente,
                            'extras'       => $extras->values(),
                        ];
                    }
                    if ($d->tipo === 'bebida') {
                        return ['tipo'=>'bebida','producto'=>['nombre'=>$d->producto->nombre ?? 'N/A']];
                    }
                    return ['tipo'=>'desconocido'];
                })->values();

                $itemsPayload[] = [
                    'tipo'          => 'promocion',
                    'promocion_id'  => $item->promocion_id,
                    'nombre'        => $item->promocion->nombre,
                    'descripcion'   => $item->promocion->descripcion,
                    'imagen'        => $item->promocion->imagen ?? null,
                    'pizzas'        => $componentes,
                    'precio_total'  => (float) $item->precio_total,
                    'desglose'      => [
                        'base'   => max(0, (float)$item->precio_total - $extrasTotal),
                        'extras' => $extrasTotal,
                    ],
                ];
            }
        }

        try {
            DB::beginTransaction();

            $pedido = new \App\Models\Pedido();
            $pedido->user_id               = $user->id;
            $pedido->estado                = ($metodo === 'stripe') ? 'pagado' : 'pendiente';
            $pedido->tipo_pedido           = $carrito->tipo_entrega ?? 'pickup';
            $pedido->metodo_pago           = $metodo;

            // Logística desde carrito
            $pedido->tipo_entrega          = $carrito->tipo_entrega;
            $pedido->sucursal_id           = $carrito->sucursal_id;
            $pedido->direccion_usuario_id  = $carrito->direccion_usuario_id;

            // Delivery + totales
            $pedido->delivery_fee          = $deliveryFee;
            $pedido->delivery_currency     = $carrito->delivery_currency;
            $pedido->delivery_distance_km  = $carrito->delivery_distance_km;
            $pedido->subtotal              = $subtotal;
            $pedido->total                 = $total;

            // Info de pago Stripe (si aplica)
            if ($metodo === 'stripe') {
                $pedido->payment_provider = 'stripe';
                $pedido->payment_ref      = $request->input('payment_intent_id');
                $pedido->payment_status   = 'paid';
                $pedido->paid_at          = now();
            }

            // Snapshot
            $pedido->detalle_json = json_encode([
                'items'    => $itemsPayload,
                'subtotal' => $subtotal,
                'delivery' => [
                    'fee'      => $deliveryFee,
                    'currency' => $carrito->delivery_currency,
                    'distance' => (float)($carrito->delivery_distance_km ?? 0),
                ],
                'total' => $total,
            ], JSON_UNESCAPED_UNICODE);

            $pedido->save();

            // Limpiar carrito (promos + extras + pivots + items)
            $itemsIds    = $carrito->items()->pluck('id');
            $detallesIds = CarritoItemPromocionDetalle::whereIn('carrito_item_id', $itemsIds)->pluck('id');
            CarritoItemsPromocionExtra::whereIn('detalle_id', $detallesIds)->delete();
            CarritoItemPromocionDetalle::whereIn('id', $detallesIds)->delete();
            DB::table('carrito_item_extra')->whereIn('carrito_item_id', $itemsIds)->delete();
            CarritoItem::whereIn('id', $itemsIds)->delete();

            // (Opcional) limpiar intent en carrito para no reusarlo
            if (\Schema::hasColumn($carrito->getTable(), 'stripe_payment_intent_id')) {
                $carrito->update(['stripe_payment_intent_id' => null]);
            }

            DB::commit();

            // Enviar factura PDF (fuera de la transacción)
            try {
                $pdf = PDF::loadView('pdf.factura', ['pedido' => $pedido])->setPaper('a4');
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(new FacturaPedidoMail($pedido, $pdf->output()));
                } else {
                    \Illuminate\Support\Facades\Log::warning('Pedido creado sin email de usuario', [
                        'pedido_id' => $pedido->id,
                        'user_id'   => $user->id
                    ]);
                }
            } catch (\Throwable $mailErr) {
                \Illuminate\Support\Facades\Log::error('Error enviando factura PDF', [
                    'pedido_id' => $pedido->id,
                    'error'     => $mailErr->getMessage(),
                ]);
            }

            return response()->json([
                'message'   => '✅ Pedido creado correctamente',
                'pedido_id' => $pedido->id,
                'total'     => $total,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ Error al procesar el pedido',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

