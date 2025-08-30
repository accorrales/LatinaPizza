<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitchenOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'cocina'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $status     = $request->input('status', 'nuevo');
        $tipoPedido = $request->input('tipo_pedido');
        $search     = trim((string) $request->input('search', ''));
        $limit      = (int) $request->input('limit', 50);

        $q = Pedido::query()
            ->with(['usuario:id,name', 'sucursal:id,nombre'])
            ->select([
                'id','user_id','sucursal_id','total','estado','tipo_pedido',
                'payment_status','paid_at',
                'kitchen_status','priority','sla_minutes','promised_at','ready_at',
                'detalle_json','created_at'
            ])
            ->whereIn('kitchen_status', ['nuevo','preparacion','listo']);

        if ($user->role !== 'admin' && !empty($user->sucursal_id)) {
            $q->where('sucursal_id', $user->sucursal_id);
        }

        if (in_array($status, ['nuevo','preparacion','listo'], true)) {
            $q->where('kitchen_status', $status);
        }

        if (in_array($tipoPedido, ['pickup','express'], true)) {
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
            ->when(!empty($user->sucursal_id), fn($qq) => $qq->where('sucursal_id', $user->sucursal_id))
            ->whereIn('kitchen_status', ['nuevo','preparacion','listo'])
            ->selectRaw("kitchen_status, COUNT(*) as c")
            ->groupBy('kitchen_status')
            ->pluck('c', 'kitchen_status');

        $data = $orders->getCollection()->map(function (Pedido $p) {
            $det = $p->detalle_json ?? [];

            $kitchenItems = [];
            foreach (($det['items'] ?? []) as $it) {
                if (($it['tipo'] ?? '') === 'producto') {
                    $label = "🍕 {$it['nombre']} — {$it['tamano']} · {$it['sabor']} · {$it['masa_nombre']}";
                    if (!empty($it['extras'])) {
                        $exn = collect($it['extras'])->pluck('nombre')->implode(', ');
                        if ($exn) {
                            $label .= " (+ {$exn})";
                        }
                    }
                    $kitchenItems[] = [
                        'tipo'  => 'producto',
                        'texto' => $label,
                        'nota'  => $it['nota_cliente'] ?? null,
                        'qty'   => (int) ($it['cantidad'] ?? 1),
                    ];
                } elseif (($it['tipo'] ?? '') === 'promocion') {
                    $label = "🎁 {$it['nombre']}";
                    $sub   = [];

                    foreach (($it['pizzas'] ?? []) as $pz) {
                        if (($pz['tipo'] ?? '') === 'pizza') {
                            // ⚠️ Nada de {$expr ?? '-'} dentro de strings.
                            $sabor = data_get($pz, 'sabor.nombre', '-');
                            $masa  = data_get($pz, 'masa.nombre', '-');
                            $extrasStr = collect($pz['extras'] ?? [])->pluck('nombre')->implode(', ');

                            $line = "🍕 {$sabor} · {$masa}";
                            if ($extrasStr) {
                                $line .= " (+ {$extrasStr})";
                            }
                            $sub[] = $line;
                        } elseif (($pz['tipo'] ?? '') === 'bebida') {
                            $bebida = data_get($pz, 'producto.nombre', 'Bebida');
                            $sub[]  = "🥤 {$bebida}";
                        }
                    }

                    $kitchenItems[] = [
                        'tipo'    => 'promocion',
                        'texto'   => $label,
                        'detalle' => $sub,
                        'qty'     => 1,
                    ];
                }
            }

            $minsWaiting = now()->diffInMinutes($p->created_at);
            $sla         = $p->sla_minutes ?: null;
            $overSla     = $sla ? max(0, $minsWaiting - $sla) : 0;

            return [
                'id'             => $p->id,
                'cliente'        => $p->usuario->name ?? 'Cliente',
                'sucursal'       => $p->sucursal->nombre ?? null,
                'tipo_pedido'    => $p->tipo_pedido,
                'total'          => (float) $p->total,
                'kitchen_status' => $p->kitchen_status,
                'priority'       => (bool) $p->priority,
                'created_at'     => $p->created_at->toIso8601String(),
                'promised_at'    => optional($p->promised_at)->toIso8601String(),
                'ready_at'       => optional($p->ready_at)->toIso8601String(),
                'mins_waiting'   => $minsWaiting,
                'sla_minutes'    => $sla,
                'over_sla'       => $overSla,
                'items'          => $kitchenItems,
                'notas'          => $p->kitchen_notes ?? null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'status'      => $status,
                'counts'      => [
                    'nuevo'       => (int) ($counts['nuevo'] ?? 0),
                    'preparacion' => (int) ($counts['preparacion'] ?? 0),
                    'listo'       => (int) ($counts['listo'] ?? 0),
                ],
                'server_time' => now()->toIso8601String(),
                'pagination'  => [
                    'current_page' => $orders->currentPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                    'last_page'    => $orders->lastPage(),
                ],
            ],
        ]);
    }

    public function show(Pedido $pedido)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'cocina'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (!empty($user->sucursal_id) && $pedido->sucursal_id !== $user->sucursal_id) {
            return response()->json(['message' => 'No autorizado a ver este pedido'], 403);
        }

        return response()->json([
            'data' => [
                'id'             => $pedido->id,
                'cliente'        => $pedido->usuario->name ?? 'Cliente',
                'sucursal'       => $pedido->sucursal->nombre ?? null,
                'tipo_pedido'    => $pedido->tipo_pedido,
                'total'          => (float) $pedido->total,
                'kitchen_status' => $pedido->kitchen_status,
                'priority'       => (bool) $pedido->priority,
                'created_at'     => $pedido->created_at->toIso8601String(),
                'promised_at'    => optional($pedido->promised_at)->toIso8601String(),
                'ready_at'       => optional($pedido->ready_at)->toIso8601String(),
                'detalle'        => $pedido->detalle_json,
                'notas'          => $pedido->kitchen_notes ?? null,
            ],
        ]);
    }
    // Helpers de autorización y de validación de sucursal
    protected function ensureKitchenAuthAndScope(?Pedido $pedido = null)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role, ['admin','cocina'])) {
            abort(403, 'No autorizado');
        }
        if ($pedido && !empty($user->sucursal_id) && $pedido->sucursal_id !== $user->sucursal_id) {
            abort(403, 'No autorizado a esta sucursal');
        }
    }

    /** PATCH /status {status: nuevo|preparacion|listo} */
    public function updateStatus(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $status = $request->input('status');
        if (!in_array($status, ['nuevo','preparacion','listo'], true)) {
            return response()->json(['message' => 'Estado inválido'], 422);
        }

        $pedido->kitchen_status = $status;

        if ($status === 'preparacion') {
            if (!$pedido->sla_minutes) {
                $pedido->sla_minutes = $pedido->tipo_pedido === 'express' ? 45 : 20; // ajusta a tu operación
            }
            if (!$pedido->promised_at) {
                $pedido->promised_at = $pedido->created_at->clone()->addMinutes($pedido->sla_minutes);
            }
        }

        if ($status === 'listo' && empty($pedido->ready_at)) {
            $pedido->ready_at = now();
        }

        $pedido->save();
        $pedido->guardarHistorial("kitchen:status:{$status}");

        return response()->json([
            'message' => 'OK',
            'data'    => [
                'id' => $pedido->id,
                'kitchen_status' => $pedido->kitchen_status,
                'promised_at'    => optional($pedido->promised_at)->toIso8601String(),
                'ready_at'       => optional($pedido->ready_at)->toIso8601String(),
            ],
        ]);
    }

    /** PATCH /priority {priority: true|false} */
    public function updatePriority(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $priority = filter_var($request->input('priority'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($priority === null) {
            return response()->json(['message'=>'priority debe ser boolean'], 422);
        }

        $pedido->priority = $priority;
        $pedido->save();

        return response()->json(['message'=>'OK', 'data'=>[
            'id'=>$pedido->id,'priority'=>(bool)$pedido->priority
        ]]);
    }

    /** PATCH /notes {notes: string} */
    public function updateNotes(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $notes = (string) $request->input('notes', '');
        if (mb_strlen($notes) > 2000) {
            return response()->json(['message'=>'notes demasiado largo'], 422);
        }

        $pedido->kitchen_notes = $notes ?: null;
        $pedido->save();

        return response()->json(['message'=>'OK', 'data'=>[
            'id'=>$pedido->id,'kitchen_notes'=>$pedido->kitchen_notes
        ]]);
    }

    /** PATCH /sla {sla_minutes: int} -> también fija promised_at si no existe */
    public function updateSla(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $sla = (int) $request->input('sla_minutes');
        if ($sla < 5 || $sla > 240) {
            return response()->json(['message'=>'sla_minutes fuera de rango (5..240)'], 422);
        }

        $pedido->sla_minutes = $sla;
        if (empty($pedido->promised_at)) {
            $pedido->promised_at = now()->addMinutes($sla);
        }
        $pedido->save();

        return response()->json(['message'=>'OK', 'data'=>[
            'id'=>$pedido->id,'sla_minutes'=>$pedido->sla_minutes,'promised_at'=>optional($pedido->promised_at)->toIso8601String()
        ]]);
    }

    /** PATCH /promised {promised_at: ISO8601|Y-m-d H:i:s} */
    public function updatePromised(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $val = $request->input('promised_at');
        try {
            $dt = \Carbon\Carbon::parse($val);
        } catch (\Throwable $e) {
            return response()->json(['message'=>'Fecha/hora inválida'], 422);
        }

        $pedido->promised_at = $dt;
        $pedido->save();

        return response()->json(['message'=>'OK', 'data'=>[
            'id'=>$pedido->id,'promised_at'=>$pedido->promised_at->toIso8601String()
        ]]);
    }

    /** PATCH /ready  (marca listo ahora mismo) */
    public function markReady(Request $request, Pedido $pedido)
    {
        $this->ensureKitchenAuthAndScope($pedido);

        $pedido->kitchen_status = 'listo';
        $pedido->ready_at = now();
        $pedido->save();

        return response()->json(['message'=>'OK', 'data'=>[
            'id'=>$pedido->id,'kitchen_status'=>$pedido->kitchen_status,'ready_at'=>$pedido->ready_at->toIso8601String()
        ]]);
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
        if (!$user || !in_array($user->role, ['admin','cocina'], true)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:pedidos,id',
            'status' => 'required|in:nuevo,preparacion,listo',
        ]);

        $affected = 0;

        DB::transaction(function () use ($user, $data, &$affected) {
            $q = Pedido::query()->whereIn('id', $data['ids']);
            if (!empty($user->sucursal_id)) {
                $q->where('sucursal_id', $user->sucursal_id);
            }
            $pedidos = $q->get();

            foreach ($pedidos as $p) {
                $p->kitchen_status = $data['status'];
                if ($data['status'] === 'listo') {
                    $p->ready_at = now();
                }
                $p->save();
                $p->guardarHistorial('kitchen:bulk:' . $data['status']);
                $affected++;
            }
        });

        return response()->json(['message' => 'Actualización masiva OK', 'affected' => $affected]);
    }
}
