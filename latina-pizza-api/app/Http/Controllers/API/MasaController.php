<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Masa;
use Illuminate\Support\Facades\DB;
class MasaController extends Controller
{
    public function index()
    {
        return response()->json(Masa::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:255|unique:masas,tipo',
            'precio_extra' => 'nullable|numeric|min:0',
        ]);

        $masa = Masa::create($validated);
        return response()->json($masa, 201);
    }

    public function show($id)
    {
        return response()->json(Masa::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $masa = Masa::findOrFail($id);
        $validated = $request->validate([
            'tipo' => 'required|string|max:255|unique:masas,tipo,'.$masa->id,
            'precio_extra' => 'nullable|numeric|min:0',
        ]);
        $masa->update($validated);
        return response()->json($masa);
    }

    public function destroy($id)
    {
        $masa = Masa::findOrFail($id);
        $inUse = DB::table('detalle_pedidos')->where('masa_id', $masa->id)->exists()
            || DB::table('detalle_pedido_promocion')->where('masa_id', $masa->id)->exists()
            || DB::table('promocion_componentes')->where('masa_id', $masa->id)->exists();
        if ($inUse) {
            return response()->json(['message' => 'La masa tiene pedidos históricos y no se puede eliminar.'], 409);
        }
        $masa->delete();
        return response()->json(['message' => 'Masa eliminada correctamente.']);
    }
}
