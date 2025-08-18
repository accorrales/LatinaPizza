<?php

namespace App\Http\Controllers\API;

use App\Models\Carrito;
use App\Models\DireccionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Validation\Rule;
class EntregaController extends Controller
{
    public function setMetodoEntrega(Request $r)
    {
        $data = $r->validate([
            'tipo'                 => 'required|in:pickup,express',
            'sucursal_id'          => 'nullable|integer|exists:sucursales,id',
            'direccion_usuario_id' => 'nullable|integer|exists:direcciones_usuario,id',
        ]);

        $carrito  = Carrito::firstOrCreate(['user_id' => Auth::id()]);
        $currency = config('delivery.currency', '₡');

        if ($data['tipo'] === 'express') {
            // Deben venir ambos IDs para poder calcular distancia/fee
            if (empty($data['direccion_usuario_id']) || empty($data['sucursal_id'])) {
                return response()->json(['error' => 'Faltan direccion_usuario_id y/o sucursal_id para express'], 422);
            }

            // La dirección debe pertenecer al usuario autenticado
            $dir = DireccionUsuario::where('user_id', Auth::id())
                    ->findOrFail($data['direccion_usuario_id']);
            $suc = Sucursal::findOrFail($data['sucursal_id']);

            // Normaliza pares lat/lon por si se guardaron invertidos o con 0..360
            [$latD, $lngD] = $this->normalizePair($dir->latitud, $dir->longitud);
            [$latS, $lngS] = $this->normalizePair($suc->latitud, $suc->longitud);
            if ($latD === null || $lngD === null || $latS === null || $lngS === null) {
                return response()->json(['error' => 'Dirección o sucursal sin coordenadas válidas'], 422);
            }

            $distKm = $this->haversine($latD, $lngD, $latS, $lngS);
            $maxKm  = (float) config('delivery.max_km', 10);
            if ($distKm > $maxKm) {
                return response()->json(['error' => 'La sucursal no cubre esa dirección'], 422);
            }

            $fee = $this->feeForDistance($distKm, config('delivery.tiers', []));

            $carrito->update([
                'tipo_entrega'          => 'express',
                'sucursal_id'           => $suc->id,
                'direccion_usuario_id'  => $dir->id,
                'delivery_fee'          => $fee,
                'delivery_distance_km'  => round($distKm, 2),
                'delivery_currency'     => $currency,
            ]);
        } else {
            // PICKUP: no hay delivery
            if (empty($data['sucursal_id'])) {
                return response()->json(['error' => 'Falta sucursal_id para pickup'], 422);
            }
            $carrito->update([
                'tipo_entrega'          => 'pickup',
                'sucursal_id'           => $data['sucursal_id'],
                'direccion_usuario_id'  => null,
                'delivery_fee'          => 0,
                'delivery_distance_km'  => null,
                'delivery_currency'     => $currency,
            ]);
        }

        return response()->json([
            'message'  => 'Método de entrega actualizado',
            'data'     => $carrito->fresh(),
            'subtotal' => round($carrito->calcSubtotal(), 2),
            'delivery' => [
                'fee'      => (float) ($carrito->delivery_fee ?? 0),
                'currency' => $carrito->delivery_currency,
                'distance' => (float) ($carrito->delivery_distance_km ?? 0),
            ],
            'total'    => round($carrito->calcTotal(), 2),
        ]);
    }

    /* ----------------- Helpers ----------------- */

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        return $R * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }

    private function feeForDistance(float $km, array $tiers): int
    {
        // Usa el primer tramo cuyo 'max' sea >= distancia
        foreach ($tiers as $t) {
            if ($km <= (float) $t['max']) return (int) $t['fee'];
        }
        // Si la distancia cae fuera de todos los tramos (no debería si validamos max_km),
        // devolvemos el último fee como fallback.
        return (int) end($tiers)['fee'] ?? 0;
    }

    private function normalizePair($lat, $lng): array
    {
        $la = is_null($lat) ? null : (float) $lat;
        $lo = is_null($lng) ? null : (float) $lng;
        if ($la === null || $lo === null) return [null, null];

        if ($lo > 180) $lo -= 360; // 0..360 → -180..180
        $looksSwapped =
            (abs($la) > 90 && abs($lo) <= 90) ||
            (abs($lo) < 20 && abs($la) > 20);
        if ($looksSwapped) { $tmp = $la; $la = $lo; $lo = $tmp; }

        $la = max(min($la, 90), -90);
        $lo = max(min($lo, 180), -180);
        return [$la, $lo];
    }
}
