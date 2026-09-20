<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.stripe.webhook_secret');
        if (!$secret) {
            return response()->json(['message' => 'Webhook no configurado.'], 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret
            );
        } catch (SignatureVerificationException|\UnexpectedValueException) {
            return response()->json(['message' => 'Firma inválida.'], 400);
        }

        try {
            $processed = DB::transaction(function () use ($event) {
                $inserted = DB::table('stripe_webhook_events')->insertOrIgnore([
                    'event_id' => $event->id,
                    'event_type' => $event->type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if ($inserted === 0) {
                    return false;
                }

                $object = $event->data->object;
                $paymentIntentId = str_starts_with($event->type, 'payment_intent.')
                    ? $object->id
                    : ($object->payment_intent ?? null);

                if (!$paymentIntentId) {
                    return true;
                }

                $pedido = Pedido::where('payment_ref', $paymentIntentId)->lockForUpdate()->first();
                if (!$pedido) {
                    Log::info('Stripe event received before order creation.', [
                        'event_id' => $event->id,
                        'payment_intent' => $paymentIntentId,
                    ]);
                    return true;
                }

                match ($event->type) {
                    'payment_intent.succeeded' => $pedido->forceFill([
                        'payment_status' => 'paid',
                        'estado' => $pedido->estado === 'cancelado' ? 'cancelado' : 'pagado',
                        'paid_at' => $pedido->paid_at ?: now(),
                    ])->save(),
                    'payment_intent.payment_failed' => $pedido->forceFill(['payment_status' => 'failed'])->save(),
                    'payment_intent.canceled' => $pedido->forceFill(['payment_status' => 'canceled'])->save(),
                    'charge.refunded' => $pedido->forceFill(['payment_status' => 'refunded'])->save(),
                    default => null,
                };

                return true;
            });

            return response()->json(['received' => true, 'processed' => $processed]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'No se pudo procesar el evento.'], 500);
        }
    }
}
