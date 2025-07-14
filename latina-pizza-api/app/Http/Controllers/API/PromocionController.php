<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;
class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with([
            'componentes.sabor',
            'componentes.tamano',
            'componentes.masa'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $promociones
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
        ]);

        $promocion = Promocion::create($validated);

        return response()->json([
            'message' => 'Promoción creada exitosamente',
            'promocion' => $promocion
        ], 201);
    }

    public function show($id)
    {
        $promociones = Promocion::with(['componentes.tamano'])->get();
        return response()->json(['data' => $promociones]);
    }

    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
        ]);

        $promocion->update($validated);

        return response()->json([
            'message' => 'Promoción actualizada correctamente',
            'promocion' => $promocion
        ]);
    }

    public function destroy($id)
    {
        $promocion = Promocion::findOrFail($id);
        $promocion->delete();

        return response()->json([
            'message' => 'Promoción eliminada exitosamente'
        ]);
    }
}

