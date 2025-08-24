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
use App\Support\Money;
use Stripe\StripeClient;
class PagoController extends Controller
{
    public function createIntent(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito()->with('items')->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['error' => 'Carrito vacío'], 422);
        }

        // ✅ Usa el breakdown que suma items + extras de promo
        $bd          = $carrito->subtotalBreakdown();               // <-- asegúrate de tener este método en el modelo
        $subtotal    = (float) $bd['subtotal'];                     // items + extras
        $deliveryFee = ($carrito->tipo_entrega === 'express') ? (float) ($carrito->delivery_fee ?? 0) : 0.0;
        $total       = round($subtotal + $deliveryFee, 2);

        // Stripe (centavos)
        $amount   = (int) round($total * 100);
        $currency = config('services.stripe.currency', 'crc');

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $intentId = $carrito->stripe_payment_intent_id ?? null;

        try {
            if ($intentId) {
                $intent = \Stripe\PaymentIntent::retrieve($intentId);

                // Si el monto o moneda no coinciden, actualiza
                if ((int)$intent->amount !== $amount || strtolower($intent->currency) !== strtolower($currency)) {
                    $intent = \Stripe\PaymentIntent::update($intent->id, [
                        'amount'   => $amount,
                        'currency' => $currency,
                    ]);
                }
            } else {
                $intent = \Stripe\PaymentIntent::create([
                    'amount'   => $amount,
                    'currency' => $currency,
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata' => [
                        'user_id'    => (string) $user->id,
                        'carrito_id' => (string) $carrito->id,
                    ],
                ]);
                if (\Schema::hasColumn($carrito->getTable(), 'stripe_payment_intent_id')) {
                    $carrito->update(['stripe_payment_intent_id' => $intent->id]);
                }
            }

            // Log opcional de control
            \Log::debug('PI_INTENT_READY', [
                'pi_id'     => $intent->id,
                'pi_amount' => $intent->amount,
                'pi_curr'   => $intent->currency,
                'calc'      => ['subtotal'=>$subtotal, 'delivery'=>$deliveryFee, 'total'=>$total, 'amount'=>$amount],
            ]);

            return response()->json([
                'client_secret'     => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'amount'            => $amount,
                'currency'          => $currency,
            ]);
        } catch (\Throwable $e) {
            \Log::error('createIntent error', ['e' => $e->getMessage()]);
            return response()->json(['error' => 'No se pudo iniciar el pago'], 500);
        }
    }
    public function intent(Request $request)
    {
        $user = Auth::user();
        $carrito = $user->carrito()->with('items')->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['error' => 'Carrito vacío'], 400);
        }

        // === DEBUG: breakdown exacto desde BD ===
        $bd         = $carrito->subtotalBreakdown(); // <- paso 1
        $deliveryFee= ($carrito->tipo_entrega === 'express') ? (float)($carrito->delivery_fee ?? 0) : 0.0;
        $subtotal   = round($carrito->calcSubtotal(), 2);
        $total      = round($subtotal + $deliveryFee, 2);
        $totalCalc  = round($bd['subtotal'] + $deliveryFee, 2);

        \Illuminate\Support\Facades\Log::debug('PI_INTENT_CREATE_BREAKDOWN', [
            'user_id'         => $user->id,
            'carrito_id'      => $carrito->id,
            'sum_items_raw'   => $bd['sum_items_raw'],
            'sum_items_x_qty' => $bd['sum_items_x_qty'],
            'sum_extras'      => $bd['sum_extras'],
            'subtotal_bd'     => $bd['subtotal'],
            'delivery'        => $deliveryFee,
            'total_calc'      => $totalCalc,
            'subtotal_view'   => $subtotal,
            'total_view'      => $total,
            'items'           => $bd['items'],
            'extras'          => $bd['extras'],
        ]);

        if ($total < 0.5) {
            return response()->json(['error' => 'Total muy bajo para tarjeta'], 400);
        }

        // Moneda original (la tuya)
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

            \Illuminate\Support\Facades\Log::debug('PI_INTENT_CREATED', [
                'pi_id'     => $intent->id,
                'pi_amount' => $intent->amount,
                'pi_curr'   => $intent->currency,
                'status'    => $intent->status,
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