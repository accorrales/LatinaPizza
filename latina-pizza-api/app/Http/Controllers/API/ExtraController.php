<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtraController extends Controller
{
    public function index()
    {
        return response()->json(Extra::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:extras,nombre',
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $extra = Extra::create($validated);

        return response()->json($extra, 201);
    }

    public function show($id)
    {
        return response()->json(Extra::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $extra = Extra::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:extras,nombre,'.$extra->id,
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $extra->update($validated);

        return response()->json($extra);
    }

    public function destroy($id)
    {
        $extra = Extra::findOrFail($id);
        $inUse = DB::table('detalle_pedido_extra')->where('extra_id', $extra->id)->exists()
            || DB::table('detalle_promocion_extra')->where('extra_id', $extra->id)->exists();
        if ($inUse) {
            return response()->json(['message' => 'El extra tiene pedidos históricos y no se puede eliminar.'], 409);
        }
        $extra->delete();

        return response()->json(['message' => 'Extra eliminado correctamente.']);
    }
}
