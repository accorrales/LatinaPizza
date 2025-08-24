<?php

namespace App\Http\Controllers\API;

use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class StripeController extends Controller
{
    /*
    public function checkout(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->carrito || $user->carrito->productos->isEmpty()) {
            return response()->json(['message' => 'Carrito vacío o usuario no válido'], 400);
        }

        DB::beginTransaction();

        try {
            // 1. Crear el pedido
            $pedido = new Pedido();
            $pedido->user_id = $user->id;
            $pedido->estado = 'pendiente';
            $pedido->tipo_pedido = $request->input('tipo_pedido', 'express');
            $pedido->sucursal_id = $user->sucursal_id;
            $pedido->save();

            foreach ($user->carrito->productos as $producto) {
                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad ?? 1,
                ]);
            }

            DB::commit();

            // 2. Crear sesión de Stripe
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $lineItems = [];

            foreach ($user->carrito->productos as $producto) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $producto->precio * 100,
                        'product_data' => [
                            'name' => $producto->nombre,
                        ],
                    ],
                    'quantity' => $producto->pivot->cantidad ?? 1,
                ];
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => url('/api/pago-exitoso?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/api/pago-cancelado'),
                'metadata' => [
                    'pedido_id' => $pedido->id,
                    'user_id' => $user->id,
                ]
            ]);

            return response()->json(['url' => $session->url]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar el pago', 'error' => $e->getMessage()], 500);
        }
    } */
}

