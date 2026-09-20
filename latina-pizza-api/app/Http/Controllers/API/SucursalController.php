<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DireccionUsuario;
use Illuminate\Support\Facades\Auth;
class SucursalController extends Controller
{
    public function index()
    {
        return Sucursal::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $sucursal = Sucursal::create($request->only('nombre', 'direccion', 'latitud', 'longitud'));

        return response()->json([
            'message' => 'Sucursal creada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    public function show($id)
    {
        return Sucursal::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $sucursal->update($request->only('nombre', 'direccion', 'latitud', 'longitud'));

        return response()->json([
            'message' => 'Sucursal actualizada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    public function destroy($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $inUse = DB::table('pedidos')->where('sucursal_id', $sucursal->id)->exists()
            || DB::table('users')->where('sucursal_id', $sucursal->id)->exists();
        if ($inUse) {
            return response()->json(['message' => 'La sucursal tiene usuarios o pedidos asociados y no se puede eliminar.'], 409);
        }
        $sucursal->delete();

        return response()->json([
            'message' => 'Sucursal eliminada correctamente'
        ]);
    }
    public function cercanas(Request $r)
    {
        $r->validate([
            'direccion_usuario_id' => 'required|integer|exists:direcciones_usuario,id'
        ]);

        $dir = DireccionUsuario::where('user_id', Auth::id())
            ->findOrFail($r->direccion_usuario_id);

        if (is_null($dir->latitud) || is_null($dir->longitud)) {
            return response()->json(['error' => 'La dirección seleccionada no tiene latitud/longitud'], 422);
        }

        $lat0 = (float) $dir->latitud;
        $lng0 = (float) $dir->longitud;

        $maxKm  = (float) config('delivery.max_km', 10);
        $tiers  = config('delivery.tiers', []);
        $curr   = config('delivery.currency', '₡');

        $sucursales = Sucursal::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get()
            ->map(function ($s) use ($lat0, $lng0, $maxKm, $tiers) {
                $dist = self::haversine($lat0, $lng0, (float)$s->latitud, (float)$s->longitud);
                $dist = round($dist, 2);

                $covered   = $dist <= $maxKm;
                $delivery  = $covered ? $this->feeForDistance($dist, $tiers) : null;

                // Adjunta campos para el frontend
                $s->distancia_km = $dist;
                $s->covered      = $covered;
                $s->delivery_fee = $delivery; // número (no string)
                return $s;
            })
            ->sortBy('distancia_km')
            ->values();

        return response()->json([
            'direccion'   => $dir,
            'sucursales'  => $sucursales,
            'max_km'      => $maxKm,
            'currency'    => $curr,
        ]);
    }

    /** Haversine en KM */
    private static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    /** Busca fee según tramos */
    private function feeForDistance(float $km, array $tiers): int
    {
        foreach ($tiers as $t) {
            if ($km <= (float) $t['max']) {
                return (int) $t['fee'];
            }
        }
        // Si supera todos, fuera de cobertura (no debería llegar aquí)
        return 0;
    }
    
}
