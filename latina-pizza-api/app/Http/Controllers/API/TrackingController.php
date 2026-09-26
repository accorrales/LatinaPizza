<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show(Request $request, Pedido $pedido)
    {
        $user = $request->user();
        $allowed = (int) $pedido->user_id === (int) $user->id || $user->role === 'admin'
            || ($user->role === 'cocina' && $user->sucursal_id && (int) $pedido->sucursal_id === (int) $user->sucursal_id)
            || ($user->role === 'delivery' && (int) $pedido->delivery_user_id === (int) $user->id);
        abort_unless($allowed, 403);

        return response()->json(['data' => [
            'id' => $pedido->id,
            'estado' => $pedido->estado,
            'tipo' => $pedido->tipo_entrega ?? $pedido->tipo_pedido,
            'location' => $pedido->liveLocation(),
        ]])->header('Cache-Control', 'no-store, private');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'estado' => 'nullable|in:activos,todos,pendiente,pagado,preparando,listo,en_camino,entregado,cancelado',
            'tipo' => 'nullable|in:express,pickup',
            'page' => 'nullable|integer|min:1',
        ]);
        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'cocina'], true), 403);
        abort_if($user->role === 'cocina' && ! $user->sucursal_id, 403, 'No tiene una sucursal asignada.');

        $query = Pedido::query()
            ->with(['usuario:id,name', 'sucursal:id,nombre', 'historial' => fn ($q) => $q->orderBy('fecha')->orderBy('id')])
            ->when($user->role === 'cocina', fn ($q) => $q->where('sucursal_id', $user->sucursal_id));
        $estado = $filters['estado'] ?? 'activos';
        if ($estado === 'activos') {
            $query->whereNotIn('estado', ['entregado', 'cancelado']);
        } elseif ($estado !== 'todos') {
            $query->where('estado', $estado);
        }
        if (! empty($filters['tipo'])) {
            $query->where('tipo_entrega', $filters['tipo']);
        }
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search) && strlen($search) < 19) {
                    $q->where('id', (int) $search)->orWhereHas('usuario', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
                } else {
                    $q->whereHas('usuario', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
                }
            });
        }

        $orders = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20);
        $orders->through(fn (Pedido $order) => [
            'id' => $order->id,
            'cliente' => $order->usuario?->name ?? 'Cliente',
            'sucursal' => $order->sucursal?->nombre ?? 'Sin sucursal',
            'tipo' => $order->tipo_entrega ?? $order->tipo_pedido,
            'estado' => $order->estado,
            'cocina' => $order->kitchen_status,
            'created_at' => $order->created_at->toIso8601String(),
            'promised_at' => $order->promised_at?->toIso8601String(),
            'ready_at' => $order->ready_at?->toIso8601String(),
            'total' => $order->total,
            'direccion' => collect($order->delivery_address_json ?? [])->only(['nombre', 'direccion_exacta', 'provincia', 'canton', 'distrito', 'telefono_contacto', 'referencias'])->all(),
            'historial' => $order->historial->map(fn ($event) => [
                'estado' => $event->estado,
                'fecha' => $event->fecha ? Carbon::parse($event->fecha)->toIso8601String() : null,
            ]),
        ]);

        return response()->json(['data' => $orders->items(), 'meta' => [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
            'server_time' => now()->toIso8601String(),
        ]])->header('Cache-Control', 'no-store, private');
    }
}
