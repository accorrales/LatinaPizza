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
            'incluye_bebida' => 'required|boolean', // ✅ Asegúrate de que esté en el request
            'componentes' => 'nullable|array',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.sabor_id' => 'nullable|exists:sabores,id',
            'componentes.*.tamano_id' => 'nullable|exists:tamanos,id',
            'componentes.*.masa_id' => 'nullable|exists:masas,id',
        ]); 
            
        $promocion = Promocion::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'precio_total' => $validated['precio_total'],
            'incluye_bebida' => $validated['incluye_bebida'], // ✅ Aquí se guarda el valor
        ]);

        if (isset($validated['componentes'])) {
            foreach ($validated['componentes'] as $componente) {
                $promocion->componentes()->create([
                    'tipo'      => $componente['tipo'],
                    'sabor_id'  => $componente['tipo'] === 'pizza' ? $componente['sabor_id'] ?? null : null,
                    'tamano_id' => $componente['tipo'] === 'pizza' ? $componente['tamano_id'] ?? null : null,
                    'masa_id'   => $componente['tipo'] === 'pizza' ? $componente['masa_id'] ?? null : null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Promoción creada exitosamente',
            'promocion' => $promocion->load('componentes.sabor', 'componentes.tamano', 'componentes.masa')
        ], 201);
    }

    public function show($id)
    {
        $promocion = Promocion::with([
            'componentes.sabor',
            'componentes.tamano',
            'componentes.masa'
        ])->findOrFail($id);

        return response()->json(['data' => $promocion]);
    }


    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|string',
            'incluye_bebida' => 'required|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer|exists:tamanos,id',
            'componentes.*.sabor_id' => 'nullable|integer|exists:sabores,id',
            'componentes.*.masa_id' => 'nullable|integer|exists:masas,id',
        ]);

        // 🔁 Actualiza la promoción
        $promocion->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'precio_total' => $validated['precio_total'],
            'precio_sugerido' => $validated['precio_sugerido'] ?? null,
            'imagen' => $validated['imagen'] ?? null,
        ]);

        // 🧹 Elimina componentes anteriores
        $promocion->componentes()->delete();

        // ➕ Crea los nuevos componentes
        foreach ($validated['componentes'] as $componente) {
            $promocion->componentes()->create([
                'tipo' => $componente['tipo'],
                'cantidad' => $componente['cantidad'],
                'sabor_id' => $componente['sabor_id'] ?? null,
                'tamano_id' => $componente['tamano_id'] ?? null,
                'masa_id' => $componente['masa_id'] ?? null,
            ]);
        }

        // 🔁 Carga relaciones para el response
        $promocion->load('componentes.sabor', 'componentes.tamano', 'componentes.masa');

        return response()->json([
            'message' => 'Promoción actualizada correctamente',
            'promocion' => $promocion,
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

