<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sabor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class SaborController extends Controller
{
    public function index(): JsonResponse
    {
        $sabores = Sabor::select('id', 'nombre', 'descripcion', 'imagen')
                        ->orderBy('nombre')
                        ->get();

        return response()->json($sabores);
    }
    // 📥 POST /api/sabores
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url',
        ]);

        $sabor = Sabor::create($validated);

        return response()->json([
            'message' => 'Sabor creado correctamente.',
            'sabor' => $sabor
        ], 201);
    }

    // 📥 GET /api/sabores/{id}
    public function show($id)
    {
        $sabor = Sabor::find($id);

        if (!$sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        return response()->json($sabor);
    }

    // 📥 PUT /api/sabores/{id}
    public function update(Request $request, $id)
    {
        $sabor = Sabor::find($id);

        if (!$sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url',
        ]);

        $sabor->update($validated);

        return response()->json([
            'message' => 'Sabor actualizado correctamente.',
            'sabor' => $sabor
        ]);
    }

    // 📥 DELETE /api/sabores/{id}
    public function destroy($id)
    {
        $sabor = Sabor::find($id);

        if (!$sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        $sabor->delete();

        return response()->json(['message' => 'Sabor eliminado correctamente.']);
    }
}