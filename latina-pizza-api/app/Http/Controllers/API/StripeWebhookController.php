<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    /*public function handle(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET'); // Lo pondrás en el .env

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid webhook signature.'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $userId = $session->metadata->user_id ?? null;
            $user = User::find($userId);

            if ($user && $user->carrito && $user->carrito->productos->isNotEmpty()) {
                try {
                    DB::beginTransaction();

                    $pedido = new Pedido();
                    $pedido->user_id = $user->id;
                    $pedido->estado = 'pendiente';
                    $pedido->tipo_pedido = 'online';
                    $pedido->sucursal_id = $user->sucursal_id;
                    $pedido->save();

                    foreach ($user->carrito->productos as $producto) {
                        $pedido->productos()->attach($producto->id, [
                            'cantidad' => $producto->pivot->cantidad ?? 1,
                        ]);
                    }

                    $user->carrito->productos()->detach();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['error' => 'Error al procesar el pedido'], 500);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }*/
}

