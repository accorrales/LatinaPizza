<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pedido;
use Stripe\Webhook;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Auth;
use App\Models\Carrito;
class PagoController extends Controller
{
    public function createIntent(Request $request)
    {
        $user = $request->user();

        /** @var \App\Models\Carrito|null $carrito */
        $carrito = $user->carrito()->with('items')->first();
        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['error' => 'Carrito vacío'], 422);
        }

        // Totales (mismo cálculo que usas en checkout)
        $subtotal    = round($carrito->calcSubtotal(), 2);
        $deliveryFee = ($carrito->tipo_entrega === 'express') ? (float) ($carrito->delivery_fee ?? 0) : 0.0;
        $total       = round($subtotal + $deliveryFee, 2);

        // Stripe trabaja en la mínima unidad (colones → céntimos)
        $amount   = (int) round($total * 100);
        $currency = config('services.stripe.currency', 'crc');

        Stripe::setApiKey(config('services.stripe.secret'));

        // Si guardas el intent en el carrito, puedes reutilizarlo
        $intentId = $carrito->stripe_payment_intent_id ?? null;

        try {
            if ($intentId) {
                $intent = PaymentIntent::retrieve($intentId);
                // Si cambió el monto o la moneda, actualiza
                if ($intent->amount !== $amount || $intent->currency !== $currency) {
                    $intent = PaymentIntent::update($intent->id, [
                        'amount'   => $amount,
                        'currency' => $currency,
                    ]);
                }
            } else {
                $intent = PaymentIntent::create([
                    'amount'                   => $amount,
                    'currency'                 => $currency,
                    'metadata'                 => [
                        'user_id' => (string) $user->id,
                        'carrito_id' => (string) $carrito->id,
                    ],
                    // Esto habilita métodos automáticos (tarjeta, etc.)
                    'automatic_payment_methods' => ['enabled' => true],
                ]);
                // (opcional) guardarlo para reusar
                if ($carrito->isFillable('stripe_payment_intent_id') || \Schema::hasColumn($carrito->getTable(), 'stripe_payment_intent_id')) {
                    $carrito->update(['stripe_payment_intent_id' => $intent->id]);
                }
            }

            return response()->json([
                'client_secret'      => $intent->client_secret,
                'payment_intent_id'  => $intent->id,
                'amount'             => $amount,
                'currency'           => $currency,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'No se pudo iniciar el pago: '.$e->getMessage()], 500);
        }
    }
    public function intent(Request $request)
    {
        $user = Auth::user();
        $carrito = $user->carrito()->with('items')->first();
        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['error' => 'Carrito vacío'], 400);
        }

        $subtotal    = round($carrito->calcSubtotal(), 2);
        $deliveryFee = $carrito->tipo_entrega === 'express' ? (float)($carrito->delivery_fee ?? 0) : 0.0;
        $total       = round($subtotal + $deliveryFee, 2);

        if ($total < 0.5) {
            return response()->json(['error' => 'Total muy bajo para tarjeta'], 400);
        }

        // PRUEBA con USD primero. Luego, si todo bien, vuelve a tu moneda si tu cuenta la soporta.
        $currency = 'crc';
        $amount   = (int) round($total * 100);

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        try {
            $intent = $stripe->paymentIntents->create([
                'amount'               => $amount,
                'currency'             => $currency,
                'payment_method_types' => ['card'],   // <-- fuerza tarjeta
                'metadata' => [
                    'user_id'    => (string)$user->id,
                    'carrito_id' => (string)$carrito->id,
                ],
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'id'            => $intent->id,
                'amount'        => $amount,
                'currency'      => $currency,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Stripe intent error', ['msg' => $e->getMessage()]);
            return response()->json(['error' => 'No se pudo crear el intento de pago'], 500);
        }
    }
}