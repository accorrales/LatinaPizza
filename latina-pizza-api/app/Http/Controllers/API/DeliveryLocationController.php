<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryLocationController extends Controller
{
    public function orders(Request $request)
    {
        $orders = Pedido::where('delivery_user_id', $request->user()->id)
            ->where('estado', Pedido::EN_CAMINO)->orderBy('id')
            ->get()->map(fn (Pedido $order) => [
                'id' => $order->id,
                'estado' => $order->estado,
                'direccion' => collect($order->delivery_address_json ?? [])->only(['nombre', 'direccion_exacta', 'referencias', 'telefono_contacto'])->all(),
            ]);

        return response()->json(['data' => $orders])->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request, Pedido $pedido)
    {
        abort_unless((int) $pedido->delivery_user_id === (int) $request->user()->id, 403);
        $values = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,10000'],
            'recorded_at' => ['required', 'date', 'after_or_equal:'.now()->subMinutes(2)->toIso8601String(), 'before_or_equal:'.now()->addSeconds(30)->toIso8601String()],
        ]);

        return DB::transaction(function () use ($request, $pedido, $values) {
            $order = Pedido::lockForUpdate()->findOrFail($pedido->id);
            abort_unless((int) $order->delivery_user_id === (int) $request->user()->id, 403);
            abort_unless($order->estado === Pedido::EN_CAMINO && ($order->tipo_entrega ?? $order->tipo_pedido) === 'express', 409, 'El pedido no está en camino.');
            // Eloquent persists date strings in the application timezone (phone timestamps are UTC).
            $recorded = Carbon::parse($values['recorded_at'])->setTimezone(config('app.timezone'));
            abort_if($order->delivery_recorded_at && $recorded->lessThanOrEqualTo($order->delivery_recorded_at), 409, 'La posición es anterior a la última recibida.');
            $order->forceFill([
                'delivery_latitude' => $values['latitude'],
                'delivery_longitude' => $values['longitude'],
                'delivery_accuracy' => $values['accuracy'] ?? null,
                'delivery_recorded_at' => $recorded,
                'delivery_received_at' => now(),
            ])->save();

            return response()->json(['data' => $order->liveLocation()])->header('Cache-Control', 'no-store, private');
        });
    }
}
