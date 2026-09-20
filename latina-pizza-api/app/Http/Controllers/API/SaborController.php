<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sabor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaborController extends Controller
{
    public function index(): JsonResponse
    {
        $sabores = Sabor::select('id', 'nombre', 'descripcion', 'imagen')
            ->orderBy('nombre')
            ->get();

        return response()->json($sabores);
    }

    public function indexConResenas(): JsonResponse
    {
        $sabores = Sabor::with(['resenas.user']) // 🔥 Relación completa
            ->whereHas('resenas')    // Solo los sabores que tienen reseñas
            ->orderBy('nombre')
            ->get()
            ->map(function ($sabor) {
                return [
                    'id' => $sabor->id,
                    'nombre' => $sabor->nombre,
                    'descripcion' => $sabor->descripcion,
                    'imagen' => $sabor->imagen,
                    'promedio_resenas' => round($sabor->resenas->avg('calificacion') ?? 0, 1),
                    'total_resenas' => $sabor->resenas->count(),
                    'resenas' => $sabor->resenas->map(function ($resena) {
                        return [
                            'id' => $resena->id,
                            'comentario' => $resena->comentario,
                            'calificacion' => $resena->calificacion,
                            'created_at' => $resena->created_at,
                            'user' => [
                                'id' => $resena->user->id ?? null,
                                'name' => $resena->user->name ?? 'Usuario desconocido',
                            ],
                        ];
                    }),
                ];
            });

        return response()->json($sabores);
    }

    // 📥 POST /api/sabores
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:sabores,nombre',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url:http,https|max:2048',
        ]);

        $sabor = Sabor::create($validated);

        return response()->json([
            'message' => 'Sabor creado correctamente.',
            'sabor' => $sabor,
        ], 201);
    }

    // 📥 GET /api/sabores/{id}
    public function show($id)
    {
        $sabor = Sabor::find($id);

        if (! $sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        return response()->json($sabor);
    }

    // 📥 PUT /api/sabores/{id}
    public function update(Request $request, $id)
    {
        $sabor = Sabor::find($id);

        if (! $sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:sabores,nombre,'.$sabor->id,
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url:http,https|max:2048',
        ]);

        $sabor->update($validated);

        return response()->json([
            'message' => 'Sabor actualizado correctamente.',
            'sabor' => $sabor,
        ]);
    }

    // 📥 DELETE /api/sabores/{id}
    public function destroy($id)
    {
        $sabor = Sabor::find($id);

        if (! $sabor) {
            return response()->json(['error' => 'Sabor no encontrado.'], 404);
        }

        $inUse = DB::table('productos')->where('sabor_id', $sabor->id)->exists()
            || DB::table('detalle_pedidos')->where('sabor_id', $sabor->id)->exists()
            || DB::table('detalle_pedido_promocion')->where('sabor_id', $sabor->id)->exists();
        if ($inUse) {
            return response()->json(['message' => 'El sabor tiene productos o pedidos históricos y no se puede eliminar.'], 409);
        }
        $sabor->delete();

        return response()->json(['message' => 'Sabor eliminado correctamente.']);
    }
}
