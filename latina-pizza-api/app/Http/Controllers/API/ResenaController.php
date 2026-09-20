<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\DetallePedidoPromocion;
use App\Models\Resena;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $comprado = DetallePedido::where('sabor_id', $saborId)->whereHas('pedido', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where(function ($status) {
                    $status->where('payment_status', 'paid')
                        ->orWhereIn('estado', ['pagado', 'entregado']);
                });
        })->exists();
        $comprado = $comprado || DetallePedidoPromocion::where('sabor_id', $saborId)
            ->whereHas('pedido', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where(function ($status) {
                        $status->where('payment_status', 'paid')
                            ->orWhereIn('estado', ['pagado', 'entregado']);
                    });
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

        $compraVerificada = DetallePedido::where('sabor_id', $request->integer('sabor_id'))->whereHas('pedido', function ($query) {
            $query->where('user_id', Auth::id())
                ->where(function ($status) {
                    $status->where('payment_status', 'paid')
                        ->orWhereIn('estado', ['pagado', 'entregado']);
                });
        })->exists();
        $compraVerificada = $compraVerificada || DetallePedidoPromocion::where('sabor_id', $request->integer('sabor_id'))
            ->whereHas('pedido', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where(function ($status) {
                        $status->where('payment_status', 'paid')
                            ->orWhereIn('estado', ['pagado', 'entregado']);
                    });
            })->exists();

        if (! $compraVerificada) {
            return response()->json(['message' => 'Solo puede reseñar sabores que haya comprado.'], 403);
        }

        $resena = Resena::updateOrCreate([
            'sabor_id' => $request->sabor_id,
            'user_id' => Auth::id(),
        ], [
            'sabor_id' => $request->sabor_id,
            'user_id' => Auth::id(),
            'comentario' => $request->comentario,
            'calificacion' => $request->calificacion,
        ]);

        return response()->json([
            'message' => 'Reseña creada exitosamente',
            'resena' => $resena,
        ], 201);
    }

    // ✅ Editar reseña
    public function update(Request $request, $id): JsonResponse
    {
        $resena = Resena::findOrFail($id);

        $user = Auth::user();

        // Solo permitir si es el autor o si es admin
        if ($resena->user_id !== $user->id && $user->role !== 'admin') {
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

        $user = Auth::user();
        if ($resena->user_id !== $user->id && $user->role !== 'admin') {
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
            'total' => $total,
        ]);
    }
}
