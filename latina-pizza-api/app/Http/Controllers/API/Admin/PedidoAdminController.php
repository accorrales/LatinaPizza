<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Refund;
use Stripe\Stripe;
use Throwable;

class PedidoAdminController extends Controller
{
    public function verPedido($id)
    {
        $pedido = Pedido::with([
            'productos',
            'detalles.sabor',
            'detalles.tamano',
            'detalles.masa',
            'detalles.extras',
            'promociones.promocion',
            'usuario',
            'sucursal',
        ])->findOrFail($id);

        return response()->json($pedido);
    }

    public function index(Request $request)
    {
        $pedidos = Pedido::with(['productos', 'usuario', 'sucursal'])
            ->when($request->filled('sucursal_id'), fn ($query) => $query->where('sucursal_id', $request->integer('sucursal_id')))
            ->orderBy('created_at', 'desc')
            ->paginate(min(max($request->integer('per_page', 25), 1), 100));

        return response()->json($pedidos);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,preparando,listo,entregado,cancelado',
        ]);

        $pedido = Pedido::findOrFail($id);
        $allowed = [
            'pendiente' => ['preparando', 'cancelado'],
            'preparando' => ['listo', 'cancelado'],
            'listo' => ['entregado', 'cancelado'],
            'entregado' => [],
            'cancelado' => [],
            'pagado' => ['preparando', 'cancelado'],
        ];
        if (! in_array($request->estado, $allowed[$pedido->estado] ?? [], true)) {
            return response()->json(['message' => 'Transición de estado no permitida.'], 422);
        }
        if ($request->estado === 'cancelado' && $pedido->payment_provider === 'stripe' && $pedido->payment_status === 'paid') {
            return response()->json(['message' => 'El pedido requiere un reembolso de Stripe antes de cancelarlo.'], 409);
        }

        $pedido->estado = $request->estado;
        $pedido->kitchen_status = match ($request->estado) {
            'preparando' => 'preparacion',
            'listo' => 'listo',
            'entregado' => 'entregado',
            'cancelado' => 'cancelado',
            default => $pedido->kitchen_status,
        };
        if ($request->estado === 'entregado' && in_array($pedido->metodo_pago, ['efectivo', 'datafono'], true)) {
            $pedido->payment_status = 'paid';
            $pedido->paid_at = $pedido->paid_at ?: now();
        }
        if ($request->estado === 'cancelado' && $pedido->payment_status === 'pending') {
            $pedido->payment_status = 'canceled';
        }
        $pedido->save();
        $pedido->guardarHistorial($request->estado);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'pedido' => $pedido,
        ]);
    }

    public function refund(Pedido $pedido)
    {
        if ($pedido->payment_provider !== 'stripe' || ! $pedido->payment_ref) {
            return response()->json(['message' => 'Este pedido no tiene un pago de Stripe reembolsable.'], 422);
        }
        if ($pedido->payment_status === 'refunded') {
            return response()->json(['message' => 'El pedido ya fue reembolsado.', 'pedido' => $pedido]);
        }
        if (! in_array($pedido->payment_status, ['paid', 'refund_pending'], true)) {
            return response()->json(['message' => 'El pago no está en un estado reembolsable.'], 409);
        }
        if (! config('services.stripe.secret')) {
            return response()->json(['message' => 'Stripe no está configurado.'], 503);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $refund = Refund::create([
                'payment_intent' => $pedido->payment_ref,
                'metadata' => ['pedido_id' => (string) $pedido->id],
            ], [
                'idempotency_key' => "refund-pedido-{$pedido->id}-{$pedido->payment_ref}",
            ]);

            if ($refund->status === 'failed') {
                return response()->json(['message' => 'Stripe rechazó el reembolso.'], 502);
            }

            $pedido = DB::transaction(function () use ($pedido, $refund) {
                $locked = Pedido::lockForUpdate()->findOrFail($pedido->id);
                $wasCanceled = $locked->estado === 'cancelado';
                $locked->forceFill([
                    'payment_status' => $refund->status === 'succeeded' ? 'refunded' : 'refund_pending',
                    'estado' => 'cancelado',
                    'kitchen_status' => 'cancelado',
                ])->save();
                if (! $wasCanceled) {
                    $locked->guardarHistorial('cancelado');
                }

                return $locked;
            });

            return response()->json([
                'message' => $refund->status === 'succeeded'
                    ? 'Reembolso completado y pedido cancelado.'
                    : 'Reembolso iniciado; Stripe todavía lo está procesando.',
                'refund_status' => $refund->status,
                'pedido' => $pedido,
            ]);
        } catch (Throwable $exception) {
            Log::error('Stripe refund failed.', [
                'pedido_id' => $pedido->id,
                'exception' => $exception::class,
            ]);

            return response()->json(['message' => 'No se pudo completar el reembolso.'], 502);
        }
    }

    public function filtrar(Request $request)
    {
        $estado = $request->query('estado');
        $tipo = $request->query('tipo_pedido');

        $query = Pedido::with(['productos', 'usuario', 'sucursal']);

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($tipo) {
            $query->where('tipo_pedido', $tipo);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->integer('sucursal_id'));
        }

        $resultados = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json($resultados);
    }

    public function tiempoEstimado()
    {
        // Configuraciones por tipo
        $config = [
            'pickup' => ['base' => 10, 'por_pedido' => 5],
            'express' => ['base' => 20, 'por_pedido' => 7],
        ];

        $estimados = [];

        foreach ($config as $tipo => $tiempo) {
            $pendientes = Pedido::where('estado', 'pendiente')
                ->where('tipo_pedido', $tipo)
                ->count();

            $total = $tiempo['base'] + ($pendientes * $tiempo['por_pedido']);

            $estimados[str_replace(' ', '_', $tipo)] = $total.' minutos';
        }

        return response()->json($estimados);
    }

    public function verHistorial($id)
    {
        $pedido = Pedido::findOrFail($id);

        $historial = $pedido->historial()->orderByDesc('fecha')->get();

        return response()->json($historial);
    }

    public function resumenSucursal($id)
    {
        // Obtener pedidos de la sucursal
        $pedidos = Pedido::with('productos')
            ->where('sucursal_id', $id)
            ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['message' => 'No hay pedidos para esta sucursal'], 404);
        }

        // Estadísticas
        $total = $pedidos->count();
        $pendientes = $pedidos->where('estado', 'pendiente')->count();
        $preparando = $pedidos->where('estado', 'preparando')->count();
        $entregados = $pedidos->where('estado', 'entregado')->count();
        $cancelados = $pedidos->where('estado', 'cancelado')->count();

        // Top productos (agrupando por ID y sumando cantidades)
        $topProductos = collect();
        foreach ($pedidos as $pedido) {
            foreach ($pedido->productos as $producto) {
                $existente = $topProductos->firstWhere('id', $producto->id);
                if ($existente) {
                    $existente->total += $producto->pivot->cantidad;
                } else {
                    $topProductos->push((object) [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'total' => $producto->pivot->cantidad,
                    ]);
                }
            }
        }

        // Top 5
        $topProductos = $topProductos->sortByDesc('total')->values()->take(5);

        $totalGanancias = Pedido::where('sucursal_id', $id)
            ->where('payment_status', 'paid')
            ->sum('total');

        return response()->json([
            'sucursal_id' => $id,
            'resumen' => [
                'total_pedidos' => $pedidos->count(),
                'total_ganancias' => $totalGanancias,
                'pendientes' => $pedidos->where('estado', 'pendiente')->count(),
                'preparando' => $pedidos->where('estado', 'preparando')->count(),
                'entregados' => $pedidos->where('estado', 'entregado')->count(),
                'cancelados' => $pedidos->where('estado', 'cancelado')->count(),
                'top_productos' => $topProductos,
            ],
        ]);
    }
}
