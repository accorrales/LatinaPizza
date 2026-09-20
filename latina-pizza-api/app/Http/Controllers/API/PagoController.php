<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class PagoController extends Controller
{
    public function createIntent(Request $request): JsonResponse
    {
        if (!config('services.stripe.secret')) {
            return response()->json(['message' => 'Stripe no está configurado.'], 503);
        }

        $user = $request->user();
        $carrito = $user->carrito()->with('items')->first();
        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 422);
        }
        if (!in_array($carrito->tipo_entrega, ['pickup', 'express'], true) || !$carrito->sucursal_id) {
            return response()->json(['message' => 'Seleccione el método de entrega y la sucursal antes de pagar.'], 422);
        }
        if ($carrito->tipo_entrega === 'express' && (!$carrito->direccion_usuario_id || $carrito->delivery_fee === null)) {
            return response()->json(['message' => 'Seleccione una dirección express válida antes de pagar.'], 422);
        }

        $subtotal = round($carrito->calcSubtotal(), 2);
        $deliveryFee = $carrito->tipo_entrega === 'express' ? (float) $carrito->delivery_fee : 0.0;
        $total = round($subtotal + $deliveryFee, 2);
        $amount = (int) round($total * 100);
        $currency = strtolower(config('services.stripe.currency', 'crc'));
        $fingerprint = hash('sha256', json_encode([
            'cart' => $carrito->id,
            'items' => $carrito->items->sortBy('id')->map->only(['id', 'cantidad', 'precio_total'])->values()->all(),
            'delivery' => [$carrito->tipo_entrega, $carrito->sucursal_id, $carrito->direccion_usuario_id, $deliveryFee],
            'amount' => $amount,
            'currency' => $currency,
        ], JSON_THROW_ON_ERROR));

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = null;
            if ($carrito->stripe_payment_intent_id) {
                $candidate = PaymentIntent::retrieve($carrito->stripe_payment_intent_id);
                $metadata = $candidate->metadata?->toArray() ?? [];
                $belongsToCart = (int) ($metadata['user_id'] ?? 0) === $user->id
                    && (int) ($metadata['carrito_id'] ?? 0) === $carrito->id;

                if ($belongsToCart && $candidate->status !== 'canceled') {
                    if ($candidate->status === 'succeeded' && ($metadata['cart_fingerprint'] ?? '') !== $fingerprint) {
                        $candidate = null;
                    } elseif ($candidate->status !== 'succeeded') {
                        $candidate = PaymentIntent::update($candidate->id, [
                            'amount' => $amount,
                            'currency' => $currency,
                            'metadata' => [
                                'user_id' => (string) $user->id,
                                'carrito_id' => (string) $carrito->id,
                                'cart_fingerprint' => $fingerprint,
                            ],
                        ]);
                    }
                    $intent = $candidate;
                }
            }

            if (!$intent) {
                $intent = PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => $currency,
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'carrito_id' => (string) $carrito->id,
                        'cart_fingerprint' => $fingerprint,
                    ],
                ], [
                    'idempotency_key' => "cart-{$carrito->id}-{$fingerprint}",
                ]);
                $carrito->update(['stripe_payment_intent_id' => $intent->id]);
            }

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $intent->status,
            ]);
        } catch (Throwable $exception) {
            Log::error('Stripe intent failed.', ['exception' => $exception::class]);
            return response()->json(['message' => 'No se pudo iniciar el pago.'], 502);
        }
    }
}
