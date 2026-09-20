<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetallePedidoPromocion;
use Illuminate\Http\Request;
use App\Models\Promocion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PromocionController extends Controller
{
    public function index()
    {
        $promociones = Promocion::with([
            'componentes.sabor',
            'componentes.tamano',
            'componentes.masa',
            'componentes.producto',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $promociones
        ]);
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'incluye_bebida' => 'required|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.tamano_id' => 'nullable|integer|exists:tamanos,id',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.sabor_id' => 'nullable|integer|exists:sabores,id',
            'componentes.*.masa_id' => 'nullable|integer|exists:masas,id',
            'componentes.*.producto_id' => 'nullable|integer|exists:productos,id',
        ])->validate();
        $this->validateComponents($validated['componentes']);

        $promocion = DB::transaction(function () use ($validated) {
            $promocion = Promocion::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_total' => $validated['precio_total'],
                'precio_sugerido' => $validated['precio_sugerido'] ?? null,
                'imagen' => $validated['imagen'] ?? null,
                'incluye_bebida' => $validated['incluye_bebida'],
            ]);

            foreach ($validated['componentes'] as $componente) {
                $promocion->componentes()->create([
                    'tipo' => $componente['tipo'],
                    'cantidad' => $componente['cantidad'],
                    'tamano_id' => $componente['tamano_id'] ?? null,
                    'sabor_id' => $componente['sabor_id'] ?? null,
                    'masa_id' => $componente['masa_id'] ?? null,
                    'producto_id' => $componente['producto_id'] ?? null,
                ]);
            }

            return $promocion;
        });

        return response()->json([
            'message' => 'Promoción creada exitosamente',
            'promocion' => $promocion->load('componentes.tamano', 'componentes.sabor', 'componentes.masa', 'componentes.producto')
        ], 201);
    }

    public function show($id)
    {
        $promocion = Promocion::with([
            'componentes.tamano',
            'componentes.masa',
            'componentes.sabor',
            'componentes.producto',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $promocion
        ]);
    }
    public function update(Request $request, $id)
    {
        $promocion = Promocion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'incluye_bebida' => 'required|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer|exists:tamanos,id',
            'componentes.*.sabor_id' => 'nullable|integer|exists:sabores,id',
            'componentes.*.masa_id' => 'nullable|integer|exists:masas,id',
            'componentes.*.producto_id' => 'nullable|integer|exists:productos,id',
        ]);
        $this->validateComponents($validated['componentes']);

        DB::transaction(function () use ($promocion, $validated) {
            $promocion->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_total' => $validated['precio_total'],
                'precio_sugerido' => $validated['precio_sugerido'] ?? null,
                'imagen' => $validated['imagen'] ?? null,
                'incluye_bebida' => $validated['incluye_bebida'],
            ]);

            $promocion->componentes()->delete();

            foreach ($validated['componentes'] as $componente) {
                $promocion->componentes()->create([
                    'tipo' => $componente['tipo'],
                    'cantidad' => $componente['cantidad'],
                    'sabor_id' => $componente['sabor_id'] ?? null,
                    'tamano_id' => $componente['tamano_id'] ?? null,
                    'masa_id' => $componente['masa_id'] ?? null,
                    'producto_id' => $componente['producto_id'] ?? null,
                ]);
            }
        });

        // 🔁 Carga relaciones para el response
        $promocion->load('componentes.sabor', 'componentes.tamano', 'componentes.masa', 'componentes.producto');

        return response()->json([
            'message' => 'Promoción actualizada correctamente',
            'promocion' => $promocion,
        ]);
    }


    public function destroy($id)
    {
        $promocion = Promocion::findOrFail($id);
        if (DetallePedidoPromocion::where('promocion_id', $promocion->id)->exists()) {
            return response()->json([
                'message' => 'La promoción tiene pedidos históricos y no se puede eliminar.',
            ], 409);
        }
        $promocion->delete();

        return response()->json([
            'message' => 'Promoción eliminada exitosamente'
        ]);
    }

    private function validateComponents(array $components): void
    {
        foreach ($components as $index => $component) {
            if ($component['tipo'] === 'pizza' && empty($component['tamano_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "componentes.{$index}.tamano_id" => 'Cada pizza debe tener un tamaño configurado.',
                ]);
            }
            if ($component['tipo'] === 'bebida' && empty($component['producto_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "componentes.{$index}.producto_id" => 'Cada bebida debe indicar un producto.',
                ]);
            }
        }
    }
}
