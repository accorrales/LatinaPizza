<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pedido;
use Stripe\Webhook;

class PagoController extends Controller
{
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $secret);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                $pedidoId = $session->metadata->pedido_id ?? null;

                if ($pedidoId) {
                    $pedido = Pedido::find($pedidoId);
                    if ($pedido) {
                        $pedido->estado = 'pagado';
                        $pedido->save();
                        Log::info("✅ Pedido {$pedido->id} marcado como pagado.");
                    }
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error en webhook Stripe: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook inválido'], 400);
        }
    }
}
