<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\FacturaPedidoMail;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\CarritoItemPromocionDetalle;
use App\Models\CarritoItemsPromocionExtra;
use App\Models\DetallePedido;
use App\Models\DetallePedidoPromocion;
use App\Models\DireccionUsuario;
use App\Models\Extra;
use App\Models\Pedido;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class CheckoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metodo_pago' => ['required', 'in:efectivo,datafono,stripe'],
            'payment_intent_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $paymentIntentId = $validated['payment_intent_id'] ?? null;

        if ($validated['metodo_pago'] === 'stripe' && ! $paymentIntentId) {
            throw ValidationException::withMessages([
                'payment_intent_id' => 'Falta la referencia del pago con tarjeta.',
            ]);
        }

        if ($paymentIntentId) {
            $existing = Pedido::where('payment_ref', $paymentIntentId)
                ->where('user_id', $user->id)
                ->first();
            if ($existing) {
                return $this->success($existing, true);
            }
        }

        try {
            $pedido = DB::transaction(function () use ($user, $validated, $paymentIntentId) {
                $carrito = Carrito::where('user_id', $user->id)->lockForUpdate()->first();
                if (! $carrito) {
                    throw ValidationException::withMessages(['carrito' => 'No existe un carrito activo.']);
                }

                $carrito->load($this->cartRelations());
                if ($carrito->items->isEmpty()) {
                    throw ValidationException::withMessages(['carrito' => 'Tu carrito está vacío.']);
                }

                $delivery = $this->quoteDelivery($carrito, $user->id);
                $direccion = $delivery['address'];
                $subtotal = round($carrito->calcSubtotal(), 2);
                $deliveryFee = $delivery['fee'];
                $total = round($subtotal + $deliveryFee, 2);
                if ($total <= 0) {
                    throw ValidationException::withMessages(['carrito' => 'El total del pedido no es válido.']);
                }

                $intent = null;
                if ($validated['metodo_pago'] === 'stripe') {
                    $intent = $this->validateStripePayment($carrito, $user->id, $paymentIntentId, $total);

                    $existing = Pedido::where('payment_ref', $intent->id)->lockForUpdate()->first();
                    if ($existing) {
                        abort_unless($existing->user_id === $user->id, 409, 'La referencia de pago ya fue utilizada.');

                        return $existing;
                    }
                }

                $snapshotItems = $this->snapshotItems($carrito);
                $sla = (int) config('kitchen.sla_by_tipo.'.$carrito->tipo_entrega, config('kitchen.default_sla', 25));

                $pedido = Pedido::create([
                    'user_id' => $user->id,
                    'sucursal_id' => $carrito->sucursal_id,
                    'estado' => $intent ? 'pagado' : 'pendiente',
                    'tipo_pedido' => $carrito->tipo_entrega,
                    'tipo_entrega' => $carrito->tipo_entrega,
                    'direccion_usuario_id' => $carrito->direccion_usuario_id,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'delivery_currency' => config('delivery.currency', 'CRC'),
                    'delivery_distance_km' => $delivery['distance'],
                    'total' => $total,
                    'metodo_pago' => $validated['metodo_pago'],
                    'payment_provider' => $intent ? 'stripe' : ($validated['metodo_pago'] === 'datafono' ? 'pos' : 'cash'),
                    'payment_ref' => $intent?->id,
                    'payment_status' => $intent ? 'paid' : 'pending',
                    'paid_at' => $intent ? now() : null,
                    'kitchen_status' => 'nuevo',
                    'priority' => $carrito->tipo_entrega === 'express',
                    'sla_minutes' => $sla,
                    'promised_at' => now()->addMinutes($sla),
                    'detalle_json' => [
                        'items' => $snapshotItems,
                        'subtotal' => $subtotal,
                        'delivery' => [
                            'fee' => $deliveryFee,
                            'currency' => config('delivery.currency', 'CRC'),
                            'distance' => (float) ($delivery['distance'] ?? 0),
                        ],
                        'total' => $total,
                    ],
                    'delivery_address_json' => $direccion?->only([
                        'nombre', 'direccion_exacta', 'provincia', 'canton', 'distrito',
                        'telefono_contacto', 'referencias', 'latitud', 'longitud',
                    ]),
                ]);

                $this->persistOrderDetails($pedido, $carrito);
                $pedido->guardarHistorial($pedido->estado);
                $this->clearCart($carrito);

                return $pedido;
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'No se pudo procesar el pedido.'], 500);
        }

        $this->sendInvoice($pedido);

        return $this->success($pedido);
    }

    private function quoteDelivery(Carrito $carrito, int $userId): array
    {
        if (! in_array($carrito->tipo_entrega, ['pickup', 'express'], true)) {
            throw ValidationException::withMessages(['tipo_entrega' => 'Seleccione retiro o entrega express.']);
        }
        if (! $carrito->sucursal_id) {
            throw ValidationException::withMessages(['sucursal_id' => 'Debe seleccionar una sucursal.']);
        }
        if ($carrito->tipo_entrega === 'pickup') {
            return ['address' => null, 'fee' => 0.0, 'distance' => null];
        }
        if (! $carrito->direccion_usuario_id) {
            throw ValidationException::withMessages(['direccion' => 'Debe seleccionar una dirección.']);
        }

        $direccion = DireccionUsuario::where('user_id', $userId)->find($carrito->direccion_usuario_id);
        $sucursal = Sucursal::find($carrito->sucursal_id);
        if (! $direccion || ! $sucursal || $direccion->latitud === null || $direccion->longitud === null
            || $sucursal->latitud === null || $sucursal->longitud === null) {
            throw ValidationException::withMessages(['direccion' => 'La dirección seleccionada no tiene ubicación válida.']);
        }

        $distance = $this->haversine(
            (float) $direccion->latitud,
            (float) $direccion->longitud,
            (float) $sucursal->latitud,
            (float) $sucursal->longitud,
        );
        if ($distance > (float) config('delivery.max_km', 10)) {
            throw ValidationException::withMessages(['direccion' => 'La dirección quedó fuera de la zona de entrega.']);
        }

        $fee = $this->feeForDistance($distance, config('delivery.tiers', []));
        $carrito->update([
            'delivery_fee' => $fee,
            'delivery_distance_km' => round($distance, 2),
            'delivery_currency' => config('delivery.currency', 'CRC'),
        ]);

        return ['address' => $direccion, 'fee' => (float) $fee, 'distance' => round($distance, 2)];
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function feeForDistance(float $distance, array $tiers): int
    {
        foreach ($tiers as $tier) {
            if ($distance <= (float) $tier['max']) {
                return (int) $tier['fee'];
            }
        }

        throw ValidationException::withMessages(['direccion' => 'No hay una tarifa configurada para esta distancia.']);
    }

    private function validateStripePayment(Carrito $carrito, int $userId, string $intentId, float $total): PaymentIntent
    {
        if (! config('services.stripe.secret')) {
            abort(503, 'Stripe no está configurado.');
        }
        if (! hash_equals((string) $carrito->stripe_payment_intent_id, $intentId)) {
            throw ValidationException::withMessages(['payment_intent_id' => 'El pago no corresponde al carrito actual.']);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $intent = PaymentIntent::retrieve($intentId);
        $currency = strtolower(config('services.stripe.currency', 'crc'));
        $metadata = $intent->metadata?->toArray() ?? [];

        if ($intent->status !== 'succeeded') {
            abort(402, 'El pago todavía no está confirmado.');
        }
        if ((int) $intent->amount !== (int) round($total * 100) || strtolower($intent->currency) !== $currency) {
            throw ValidationException::withMessages(['payment_intent_id' => 'El monto del pago no coincide con el pedido.']);
        }
        if ((int) ($metadata['user_id'] ?? 0) !== $userId || (int) ($metadata['carrito_id'] ?? 0) !== $carrito->id) {
            abort(403, 'El pago no pertenece a este usuario o carrito.');
        }

        return $intent;
    }

    private function snapshotItems(Carrito $carrito): array
    {
        return $carrito->items->map(function (CarritoItem $item) {
            if ($item->producto_id) {
                return [
                    'tipo' => 'producto',
                    'producto_id' => $item->producto_id,
                    'nombre' => $item->producto?->nombre,
                    'sabor' => $item->producto?->sabor?->nombre,
                    'tamano' => $item->producto?->tamano?->nombre,
                    'masa' => $item->masa?->tipo,
                    'cantidad' => (int) $item->cantidad,
                    'nota_cliente' => $item->nota_cliente,
                    'precio_total' => (float) $item->precio_total,
                    'extras' => $item->extras->map(fn (Extra $extra) => [
                        'id' => $extra->id,
                        'nombre' => $extra->nombre,
                        'precio' => $this->extraPrice($extra, $item->producto?->tamano?->nombre ?? ''),
                    ])->values()->all(),
                ];
            }

            return [
                'tipo' => 'promocion',
                'promocion_id' => $item->promocion_id,
                'nombre' => $item->promocion?->nombre,
                'descripcion' => $item->promocion?->descripcion,
                'cantidad' => (int) ($item->cantidad ?: 1),
                'precio_total' => (float) $item->precio_total,
                'componentes' => $item->detallesPromocion->map(fn ($detail) => [
                    'tipo' => $detail->tipo,
                    'producto_id' => $detail->producto_id,
                    'producto' => $detail->producto?->nombre,
                    'sabor_id' => $detail->sabor_id,
                    'sabor' => $detail->sabor?->nombre,
                    'tamano_id' => $detail->tamano_id,
                    'tamano' => $detail->tamano?->nombre,
                    'masa_id' => $detail->masa_id,
                    'masa' => $detail->masa?->tipo,
                    'nota_cliente' => $detail->nota_cliente,
                    'extras' => $detail->extras->map(fn ($extra) => [
                        'id' => $extra->extra_id,
                        'nombre' => $extra->extra?->nombre,
                        'precio' => (float) $extra->precio,
                    ])->values()->all(),
                ])->values()->all(),
            ];
        })->values()->all();
    }

    private function persistOrderDetails(Pedido $pedido, Carrito $carrito): void
    {
        foreach ($carrito->items as $item) {
            if ($item->producto_id && $item->producto) {
                $detail = DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'sabor_id' => $item->producto->sabor_id,
                    'tamano_id' => $item->producto->tamano_id,
                    'masa_id' => $item->masa_id,
                    'cantidad' => max(1, (int) $item->cantidad),
                    'nota_cliente' => $item->nota_cliente,
                    'precio_total' => $item->precio_total,
                ]);

                $extraData = [];
                foreach ($item->extras as $extra) {
                    $extraData[$extra->id] = [
                        'precio_extra' => $this->extraPrice($extra, $item->producto->tamano?->nombre ?? ''),
                    ];
                }
                $detail->extras()->attach($extraData);
                $pedido->productos()->attach($item->producto_id, ['cantidad' => max(1, (int) $item->cantidad)]);

                continue;
            }

            $promotionTotalAllocated = false;
            foreach ($item->detallesPromocion as $cartDetail) {
                $detail = DetallePedidoPromocion::create([
                    'pedido_id' => $pedido->id,
                    'promocion_id' => $item->promocion_id,
                    'producto_id' => $cartDetail->producto_id,
                    'sabor_id' => $cartDetail->sabor_id,
                    'tamano_id' => $cartDetail->tamano_id,
                    'masa_id' => $cartDetail->masa_id,
                    'nota_cliente' => $cartDetail->nota_cliente,
                    'cantidad' => 1,
                    // Keep the normalized detail sum equal to the cart line total.
                    'precio_total' => $promotionTotalAllocated ? 0 : $item->precio_total,
                ]);
                $promotionTotalAllocated = true;

                $extraData = [];
                foreach ($cartDetail->extras as $extra) {
                    $extraData[$extra->extra_id] = ['precio_extra' => $extra->precio];
                }
                $detail->extras()->attach($extraData);
            }
        }
    }

    private function clearCart(Carrito $carrito): void
    {
        $itemIds = $carrito->items->pluck('id');
        $detailIds = CarritoItemPromocionDetalle::whereIn('carrito_item_id', $itemIds)->pluck('id');
        CarritoItemsPromocionExtra::whereIn('detalle_id', $detailIds)->delete();
        CarritoItemPromocionDetalle::whereIn('id', $detailIds)->delete();
        DB::table('carrito_item_extra')->whereIn('carrito_item_id', $itemIds)->delete();
        CarritoItem::whereIn('id', $itemIds)->delete();
        $carrito->update(['stripe_payment_intent_id' => null]);
    }

    private function cartRelations(): array
    {
        return [
            'items.producto.tamano', 'items.producto.sabor', 'items.masa', 'items.extras',
            'items.promocion', 'items.detallesPromocion.sabor', 'items.detallesPromocion.tamano',
            'items.detallesPromocion.masa', 'items.detallesPromocion.producto',
            'items.detallesPromocion.extras.extra',
        ];
    }

    private function extraPrice(Extra $extra, string $size): float
    {
        $size = mb_strtolower($size);

        return match (true) {
            str_contains($size, 'extra') => (float) ($extra->precio_extragrande ?? 0),
            str_contains($size, 'grande') => (float) ($extra->precio_grande ?? 0),
            str_contains($size, 'mediana') => (float) ($extra->precio_mediana ?? 0),
            default => (float) ($extra->precio_pequena ?? 0),
        };
    }

    private function sendInvoice(Pedido $pedido): void
    {
        try {
            $pedido->load(['usuario', 'sucursal', 'direccionUsuario']);
            if (! $pedido->usuario?->email) {
                return;
            }
            $pdf = Pdf::loadView('pdf.factura', ['pedido' => $pedido])->setPaper('a4');
            Mail::to($pedido->usuario->email)->send(new FacturaPedidoMail($pedido, $pdf->output()));
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar la factura.', [
                'pedido_id' => $pedido->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function success(Pedido $pedido, bool $idempotent = false): JsonResponse
    {
        return response()->json([
            'message' => $idempotent ? 'El pedido ya había sido procesado.' : 'Pedido creado correctamente.',
            'pedido_id' => $pedido->id,
            'total' => (float) $pedido->total,
            'idempotent' => $idempotent,
        ], $idempotent ? 200 : 201);
    }
}
