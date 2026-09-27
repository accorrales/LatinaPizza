<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\DeliveryRoutePlanner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryLocationController extends Controller
{
    public function __construct(private readonly DeliveryRoutePlanner $routePlanner)
    {
    }

    public function orders(Request $request)
    {
        $orders = Pedido::where('delivery_user_id', $request->user()->id)
            ->where('estado', Pedido::EN_CAMINO)->orderBy('id')
            ->get()->map(fn (Pedido $order) => [
                'id' => $order->id,
                'estado' => $order->estado,
                'direccion' => collect($order->delivery_address_json ?? [])->only([
                    'nombre', 'direccion_exacta', 'provincia', 'canton', 'distrito', 'referencias', 'telefono_contacto', 'latitud', 'longitud',
                ])->all(),
            ]);

        return response()->json(['data' => $orders])->header('Cache-Control', 'no-store, private');
    }

    public function route(Request $request)
    {
        return response()->json(['data' => $this->routePlanner->planForDriver((int) $request->user()->id)])
            ->header('Cache-Control', 'no-store, private');
    }

    public function updateAll(Request $request)
    {
        [$location, $orderIds] = $this->persistLocation($request);

        return response()->json(['data' => [
            'location' => $location,
            'order_ids' => $orderIds,
        ]])->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request, Pedido $pedido)
    {
        abort_unless((int) $pedido->delivery_user_id === (int) $request->user()->id, 403);
        abort_unless($pedido->estado === Pedido::EN_CAMINO && ($pedido->tipo_entrega ?? $pedido->tipo_pedido) === 'express', 409, 'El pedido no está en camino.');

        [$location] = $this->persistLocation($request, $pedido->id);

        return response()->json(['data' => $location])->header('Cache-Control', 'no-store, private');
    }

    private function persistLocation(Request $request, ?int $requiredOrderId = null): array
    {
        $values = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'between:0,10000'],
            'recorded_at' => ['required', 'date', 'after_or_equal:'.now()->subMinutes(2)->toIso8601String(), 'before_or_equal:'.now()->addSeconds(30)->toIso8601String()],
        ]);

        return DB::transaction(function () use ($request, $requiredOrderId, $values) {
            $orders = Pedido::query()
                ->where('delivery_user_id', $request->user()->id)
                ->where('estado', Pedido::EN_CAMINO)
                ->lockForUpdate()
                ->get();

            abort_if($orders->isEmpty(), 409, 'No hay entregas activas asignadas.');
            if ($requiredOrderId !== null) {
                abort_unless($orders->contains(fn (Pedido $order) => (int) $order->id === $requiredOrderId), 409, 'El pedido no está en camino.');
            }

            foreach ($orders as $order) {
                abort_unless(($order->tipo_entrega ?? $order->tipo_pedido) === 'express', 409, 'Solo los pedidos express pueden compartir ubicación.');
            }

            // Eloquent persists date strings in the application timezone (phone timestamps are UTC).
            $recorded = Carbon::parse($values['recorded_at'])->setTimezone(config('app.timezone'));
            $latest = $orders->max(fn (Pedido $order) => $order->delivery_recorded_at?->timestamp ?? 0);
            abort_if($latest && $recorded->timestamp <= $latest, 409, 'La posición es anterior a la última recibida.');

            $payload = [
                'delivery_latitude' => $values['latitude'],
                'delivery_longitude' => $values['longitude'],
                'delivery_accuracy' => $values['accuracy'] ?? null,
                'delivery_recorded_at' => $recorded,
                'delivery_received_at' => now(),
            ];
            foreach ($orders as $order) {
                $order->forceFill($payload)->save();
            }

            return [$orders->first()->fresh()->liveLocation(), $orders->pluck('id')->values()->all()];
        });
    }
}
