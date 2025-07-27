<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use App\Models\Sabor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\DetallePedido;

class ResenaController extends Controller
{
    // ✅ Listar reseñas por producto
    public function index($saborId): JsonResponse
    {
        $resenas = Resena::with('user')
                        ->where('sabor_id', $saborId) // ✅ Arreglado aquí
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($resenas);
    }

    public function verificarCompra($saborId)
    {
        $user = Auth::user();

        $comprado = DetallePedido::whereHas('producto', function ($query) use ($saborId) {
            $query->where('sabor_id', $saborId);
        })->whereHas('pedido', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

        return response()->json(['comprado' => $comprado]);
    }

    // ✅ Crear nueva reseña para un producto
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'sabor_id' => 'required|exists:sabores,id',
            'comentario' => 'nullable|string',
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $resena = Resena::create([
            'sabor_id' => $request->sabor_id,
            'user_id' => Auth::id(),
            'comentario' => $request->comentario,
            'calificacion' => $request->calificacion,
        ]);

        return response()->json([
            'message' => 'Reseña creada exitosamente',
            'resena' => $resena
        ], 201);
    }

    // ✅ Editar reseña
    public function update(Request $request, $id): JsonResponse
    {
        $resena = Resena::findOrFail($id);

        $user = Auth::user();

        // Solo permitir si es el autor o si es admin
        if ($resena->user_id !== $user->id && $user->rol !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'comentario' => 'nullable|string',
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $resena->update([
            'comentario' => $request->comentario,
            'calificacion' => $request->calificacion,
        ]);

        return response()->json(['message' => 'Reseña actualizada']);
    }


    // ✅ Eliminar reseña
    public function destroy($id): JsonResponse
    {
        $resena = Resena::findOrFail($id);

        if ($resena->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $resena->delete();

        return response()->json(['message' => 'Reseña eliminada']);
    }

    // ✅ Obtener promedio por producto
    public function promedio($saborId): JsonResponse
    {
        $promedio = Resena::where('sabor_id', $saborId)->avg('calificacion');
        $total = Resena::where('sabor_id', $saborId)->count();

        return response()->json([
            'promedio' => round($promedio ?? 0, 1),
            'total' => $total
        ]);
    }
    public function indexAdmin()
    {
        $resenas = Resena::with(['sabor', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('admin.resenas.index', compact('resenas'));
    }
}

