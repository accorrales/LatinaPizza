<?php

namespace App\Http\Controllers\Api;

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
            'direccion' => 'required|string|max:255'
        ]);

        $sucursal = Sucursal::create($request->only('nombre', 'direccion'));

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
            'direccion' => 'required|string|max:255'
        ]);

        $sucursal->update($request->only('nombre', 'direccion'));

        return response()->json([
            'message' => 'Sucursal actualizada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    public function destroy($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->delete();

        return response()->json([
            'message' => 'Sucursal eliminada correctamente'
        ]);
    }
    public function cercanas(Request $r)
    {
        $r->validate(['direccion_usuario_id' => 'required|integer|exists:direcciones_usuario,id']);

        // seguridad: que la dirección sea del user
        $dir = DireccionUsuario::where('user_id', Auth::id())->findOrFail($r->direccion_usuario_id);
        if (!$dir->latitud || !$dir->longitud) {
            return response()->json(['error' => 'La dirección no tiene latitud/longitud'], 422);
        }

        $lat = (float)$dir->latitud;
        $lng = (float)$dir->longitud;

        // Haversine (distancia en km)
        $sucursales = Sucursal::select([
                'sucursales.*',
                DB::raw("ROUND(6371 * acos(cos(radians($lat)) * cos(radians(latitud)) * cos(radians(longitud) - radians($lng)) + sin(radians($lat)) * sin(radians(latitud))), 2) as distancia_km")
            ])
            ->orderBy('distancia_km')
            ->get();

        return response()->json([
            'direccion'  => $dir,
            'sucursales' => $sucursales,
        ]);
    }
}

