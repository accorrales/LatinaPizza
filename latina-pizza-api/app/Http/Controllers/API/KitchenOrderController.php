<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitchenOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['admin', 'cocina'], true)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        if ($user->role === 'cocina' && ! $user->sucursal_id) {
            return response()->json(['message' => 'El usuario de cocina no tiene una sucursal asignada.'], 403);
        }

        $status = $request->input('status', 'nuevo');
        $tipoPedido = $request->input('tipo_pedido');
        $search = trim((string) $request->input('search', ''));
        $limit = (int) $request->input('limit', 50);

        $q = Pedido::query()
            ->with([
                'usuario:id,name',
                'sucursal:id,nombre',
                'mesaSesion:id,mesa_id,mesero_user_id',
                'mesaSesion.mesa:id,numero,nombre',
            ])
            ->select([
                'id', 'user_id', 'sucursal_id', 'total', 'estado', 'tipo_pedido',
                'canal_venta', 'mesa_sesion_id', 'payment_status', 'paid_at',
                'kitchen_status', 'priority', 'sla_minutes', 'promised_at', 'ready_at',
                'detalle_json', 'kitchen_notes', 'created_at',
            ])
            ->whereNotIn('estado', ['cancelado', 'entregado', 'en_camino'])
            ->whereIn('kitchen_status', ['nuevo', 'preparacion', 'listo']);

        if ($user->role === 'cocina') {
            $q->where('sucursal_id', $user->sucursal_id);
        }

        if (in_array($status, ['nuevo', 'preparacion', 'listo'], true)) {
            $q->where('kitchen_status', $status);
        }

        if (in_array($tipoPedido, ['pickup', 'express', 'salon'], true)) {
            $q->where('tipo_pedido', $tipoPedido);
        }

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                if (ctype_digit($search)) {
                    $qq->orWhere('id', (int) $search);
                }
                $qq->orWhereHas('usuario', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            });
        }

        $q->orderByDesc('priority')->orderBy('created_at', 'asc');

        $orders = $q->paginate($limit);

        $counts = Pedido::query()
            ->when($user->role === 'cocina', fn ($qq) => $qq->where('sucursal_id', $user->sucursal_id))
            ->whereNotIn('estado', ['cancelado', 'entregado', 'en_camino'])
            ->whereIn('kitchen_status', ['nuevo', 'preparacion', 'listo'])
            ->selectRaw('kitchen_status, COUNT(*) as c')
            ->groupBy('kitchen_status')
            ->pluck('c', 'kitchen_status');

        $data = $orders->getCollection()->map(function (Pedido $p) {
            $det = $p->detalle_json ?? [];
            $kitchenItems = $this->kitchenItems($det);
            $minsWaiting = now()->diffInMinutes($p->created_at);
            $sla = $p->sla_minutes ?: null;
            $overSla = $sla ? max(0, $minsWaiting - $sla) : 0;
            $round = (int) data_get($det, 'ronda', 0);
            $tableNumber = $p->mesaSesion?->mesa?->numero;
            $customer = $p->canal_venta === Pedido::CANAL_SALON
                ? 'Mesa '.($tableNumber ?: '?').' · Ronda '.($round ?: $p->id)
                : ($p->usuario?->name ?? 'Cliente');

            return [
                'id' => $p->id,
                'cliente' => $customer,
                'sucursal' => $p->sucursal?->nombre,
                'tipo_pedido' => $p->tipo_pedido,
                'canal_venta' => $p->canal_venta,
                'mesa' => $tableNumber,
                'ronda' => $round ?: null,
                'total' => (float) $p->total,
                'kitchen_status' => $p->kitchen_status,
                'priority' => (bool) $p->priority,
                'created_at' => $p->created_at->toIso8601String(),
                'promised_at' => optional($p->promised_at)->toIso8601String(),
                'ready_at' => optional($p->ready_at)->toIso8601String(),
                'mins_waiting' => $minsWaiting,
                'sla_minutes' => $sla,
                'over_sla' => $overSla,
                'items' => $kitchenItems,
                'notas' => $p->kitchen_notes,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'status' => $status,
                'counts' => [
                    'nuevo' => (int) ($counts['nuevo'] ?? 0),
                    'preparacion' => (int) ($counts['preparacion'] ?? 0),
                    'listo' => (int) ($counts['listo'] ?? 0),
                ],
                'server_time' => now()->toIso8601String(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ],
        ]);
    }

    public function show(Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);
        $pedido->loadMissing(['usuario:id,name', 'sucursal:id,nombre', 'mesaSesion.mesa:id,numero,nombre']);
        $det = $pedido->detalle_json ?? [];
        $round = (int) data_get($det, 'ronda', 0);
        $tableNumber = $pedido->mesaSesion?->mesa?->numero;
        $customer = $pedido->canal_venta === Pedido::CANAL_SALON
            ? 'Mesa '.($tableNumber ?: '?').' · Ronda '.($round ?: $pedido->id)
            : ($pedido->usuario?->name ?? 'Cliente');

        return response()->json([
            'data' => [
                'id' => $pedido->id,
                'cliente' => $customer,
                'sucursal' => $pedido->sucursal?->nombre,
                'tipo_pedido' => $pedido->tipo_pedido,
                'canal_venta' => $pedido->canal_venta,
                'mesa' => $tableNumber,
                'ronda' => $round ?: null,
                'total' => (float) $pedido->total,
                'kitchen_status' => $pedido->kitchen_status,
                'priority' => (bool) $pedido->priority,
                'created_at' => $pedido->created_at->toIso8601String(),
                'promised_at' => optional($pedido->promised_at)->toIso8601String(),
                'ready_at' => optional($pedido->ready_at)->toIso8601String(),
                'detalle' => $pedido->detalle_json,
                'notas' => $pedido->kitchen_notes,
            ],
        ]);
    }

    protected function ensureKitchenAuthAndScope(?Pedido $pedido = null)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, ['admin', 'cocina'], true)) {
            abort(403, 'No autorizado');
        }
        if ($user->role === 'cocina' && ! $user->sucursal_id) {
            abort(403, 'El usuario de cocina no tiene una sucursal asignada');
        }
        if ($pedido && $user->role === 'cocina' && (int) $pedido->sucursal_id !== (int) $user->sucursal_id) {
            abort(403, 'No autorizado a esta sucursal');
        }
    }

    public function updateStatus(Request $request, Pedido $pedido)
    {
        return DB::transaction(function () use ($request, $pedido) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedido->id);
            $this->ensureKitchenAuthAndScope($pedido);

            $status = $request->input('status');
            if (! in_array($status, ['nuevo', 'preparacion', 'listo'], true)) {
                return response()->json(['message' => 'Estado inválido'], 422);
            }
            $transitions = [
                'nuevo' => ['nuevo', 'preparacion'],
                'preparacion' => ['preparacion', 'listo'],
                'listo' => ['listo'],
            ];
            if (! in_array($status, $transitions[$pedido->kitchen_status] ?? [], true)) {
                return response()->json(['message' => 'No se puede retroceder el estado de cocina.'], 422);
            }
            if (in_array($pedido->estado, ['cancelado', 'entregado', 'en_camino'], true)) {
                return response()->json(['message' => 'El pedido ya está cerrado.'], 409);
            }

            $pedido->kitchen_status = $status;

            if ($status === 'preparacion') {
                if (! $pedido->sla_minutes) {
                    $pedido->sla_minutes = $pedido->tipo_pedido === 'express' ? 45 : 20;
                }
                if (! $pedido->promised_at) {
                    $pedido->promised_at = $pedido->created_at->clone()->addMinutes($pedido->sla_minutes);
                }
            }

            if ($status === 'listo' && empty($pedido->ready_at)) {
                $pedido->ready_at = now();
            }

            $pedido->estado = match ($status) {
                'preparacion' => 'preparando',
                'listo' => 'listo',
                default => $pedido->estado,
            };
            $pedido->save();
            $pedido->guardarHistorial("kitchen:status:{$status}");

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'id' => $pedido->id,
                    'kitchen_status' => $pedido->kitchen_status,
                    'promised_at' => optional($pedido->promised_at)->toIso8601String(),
                    'ready_at' => optional($pedido->ready_at)->toIso8601String(),
                ],
            ]);
        });
    }

    public function updatePriority(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $priority = filter_var($request->input('priority'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($priority === null) {
            return response()->json(['message' => 'priority debe ser boolean'], 422);
        }

        $pedido->priority = $priority;
        $pedido->save();

        return response()->json(['message' => 'OK', 'data' => [
            'id' => $pedido->id, 'priority' => (bool) $pedido->priority,
        ]]);
    }

    public function updateNotes(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $notes = (string) $request->input('notes', '');
        if (mb_strlen($notes) > 2000) {
            return response()->json(['message' => 'notes demasiado largo'], 422);
        }

        $pedido->kitchen_notes = $notes ?: null;
        $pedido->save();

        return response()->json(['message' => 'OK', 'data' => [
            'id' => $pedido->id, 'kitchen_notes' => $pedido->kitchen_notes,
        ]]);
    }

    public function updateSla(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $sla = (int) $request->input('sla_minutes');
        if ($sla < 5 || $sla > 240) {
            return response()->json(['message' => 'sla_minutes fuera de rango (5..240)'], 422);
        }

        $pedido->sla_minutes = $sla;
        if (empty($pedido->promised_at)) {
            $pedido->promised_at = now()->addMinutes($sla);
        }
        $pedido->save();

        return response()->json(['message' => 'OK', 'data' => [
            'id' => $pedido->id,
            'sla_minutes' => $pedido->sla_minutes,
            'promised_at' => $pedido->promised_at?->toIso8601String(),
        ]]);
    }

    public function updatePromised(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        try {
            $dt = Carbon::parse($request->input('promised_at'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Fecha/hora inválida'], 422);
        }

        $pedido->promised_at = $dt;
        $pedido->save();

        return response()->json(['message' => 'OK', 'data' => [
            'id' => $pedido->id,
            'promised_at' => $pedido->promised_at->toIso8601String(),
        ]]);
    }

    public function markReady(Request $request, Pedido $pedido)
    {
        return DB::transaction(function () use ($pedido) {
            $pedido = Pedido::lockForUpdate()->findOrFail($pedido->id);
            $this->ensureKitchenAuthAndScope($pedido);
            if (in_array($pedido->estado, ['cancelado', 'entregado', 'en_camino'], true)) {
                return response()->json(['message' => 'El pedido ya está cerrado.'], 409);
            }

            $pedido->kitchen_status = 'listo';
            $pedido->estado = 'listo';
            $pedido->ready_at = now();
            $pedido->save();

            return response()->json(['message' => 'OK', 'data' => [
                'id' => $pedido->id,
                'kitchen_status' => $pedido->kitchen_status,
                'ready_at' => $pedido->ready_at->toIso8601String(),
            ]]);
        });
    }

    public function take(Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);
        $user = Auth::user();

        if ($pedido->taken_by_user_id && $pedido->taken_by_user_id !== $user->id) {
            return response()->json(['message' => 'Ya tomado por otro usuario'], 409);
        }

        $pedido->forceFill(['taken_by_user_id' => $user->id])->save();
        $pedido->guardarHistorial('kitchen:take');

        return response()->json([
            'message' => 'Pedido tomado',
            'taken_by_user_id' => $user->id,
        ]);
    }

    public function release(Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);
        $user = Auth::user();

        if ($pedido->taken_by_user_id && $pedido->taken_by_user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Solo quien lo tomó o un admin puede liberarlo'], 403);
        }

        $pedido->forceFill(['taken_by_user_id' => null])->save();
        $pedido->guardarHistorial('kitchen:release');

        return response()->json(['message' => 'Pedido liberado']);
    }

    public function bulkStatus(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['admin', 'cocina'], true)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:pedidos,id',
            'status' => 'required|in:nuevo,preparacion,listo',
        ]);

        $affected = 0;

        DB::transaction(function () use ($user, $data, &$affected) {
            $q = Pedido::query()->whereIn('id', $data['ids']);
            if ($user->role === 'cocina') {
                abort_unless($user->sucursal_id, 403, 'Sucursal no asignada');
                $q->where('sucursal_id', $user->sucursal_id);
            }
            $pedidos = $q->lockForUpdate()->get();

            foreach ($pedidos as $p) {
                if (in_array($p->estado, ['cancelado', 'entregado', 'en_camino'], true)) {
                    continue;
                }
                $transitions = [
                    'nuevo' => ['nuevo', 'preparacion'],
                    'preparacion' => ['preparacion', 'listo'],
                    'listo' => ['listo'],
                ];
                if (! in_array($data['status'], $transitions[$p->kitchen_status] ?? [], true)) {
                    continue;
                }
                $p->kitchen_status = $data['status'];
                $p->estado = match ($data['status']) {
                    'preparacion' => 'preparando',
                    'listo' => 'listo',
                    default => $p->estado,
                };
                if ($data['status'] === 'listo') {
                    $p->ready_at = now();
                }
                $p->save();
                $p->guardarHistorial('kitchen:bulk:'.$data['status']);
                $affected++;
            }
        });

        return response()->json(['message' => 'Actualización masiva OK', 'affected' => $affected]);
    }

    private function kitchenItems(array $detail): array
    {
        $kitchenItems = [];

        foreach (($detail['items'] ?? []) as $item) {
            if (($item['tipo'] ?? '') === 'producto') {
                $masa = $item['masa'] ?? $item['masa_nombre'] ?? '-';
                $label = '🍕 '.($item['nombre'] ?? 'Producto').' — '.($item['tamano'] ?? '-').' · '.($item['sabor'] ?? '-').' · '.$masa;
                if (! empty($item['extras'])) {
                    $extraNames = collect($item['extras'])->pluck('nombre')->filter()->implode(', ');
                    if ($extraNames) {
                        $label .= ' (+ '.$extraNames.')';
                    }
                }
                $kitchenItems[] = [
                    'tipo' => 'producto',
                    'texto' => $label,
                    'nota' => $item['nota_cliente'] ?? null,
                    'qty' => (int) ($item['cantidad'] ?? 1),
                ];

                continue;
            }

            if (($item['tipo'] ?? '') === 'promocion') {
                $sub = [];
                foreach (($item['componentes'] ?? $item['pizzas'] ?? []) as $component) {
                    if (($component['tipo'] ?? '') === 'pizza') {
                        $flavor = is_array($component['sabor'] ?? null)
                            ? data_get($component, 'sabor.nombre', '-')
                            : ($component['sabor'] ?? '-');
                        $mass = is_array($component['masa'] ?? null)
                            ? data_get($component, 'masa.nombre', '-')
                            : ($component['masa'] ?? '-');
                        $extraNames = collect($component['extras'] ?? [])->pluck('nombre')->filter()->implode(', ');
                        $line = '🍕 '.$flavor.' · '.$mass;
                        if ($extraNames) {
                            $line .= ' (+ '.$extraNames.')';
                        }
                        $sub[] = $line;
                    } elseif (($component['tipo'] ?? '') === 'bebida') {
                        $drink = is_array($component['producto'] ?? null)
                            ? data_get($component, 'producto.nombre', 'Bebida')
                            : ($component['producto'] ?? 'Bebida');
                        $sub[] = '🥤 '.$drink;
                    }
                }
                $kitchenItems[] = [
                    'tipo' => 'promocion',
                    'texto' => '🎁 '.($item['nombre'] ?? 'Promoción'),
                    'detalle' => $sub,
                    'qty' => (int) ($item['cantidad'] ?? 1),
                ];
            }
        }

        return $kitchenItems;
    }
}
