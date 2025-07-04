<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocion;
class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::all();

        return response()->json([
            'message' => 'Lista de promociones',
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
        $promocion = Promocion::with([
            'detalles.sabor',
            'detalles.tamano',
            'detalles.masa'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detalle de la promoción',
            'data' => $promocion
        ]);
    }

    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'sometimes|numeric|min:0',
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

