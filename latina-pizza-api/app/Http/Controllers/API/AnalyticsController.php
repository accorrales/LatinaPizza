<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /* =======================
       Helpers base de consulta
       ======================= */
    protected function baseQuery(Request $request)
    {
        $q = Pedido::query()->where('payment_status', 'paid')->whereNotNull('paid_at');

        // Filtros opcionales por querystring
        if ($suc = $request->query('sucursal_id')) {
            $q->where('sucursal_id', $suc);
        }
        if ($tipo = $request->query('tipo_pedido')) {
            $q->where('tipo_pedido', $tipo);
        }

        return $q;
    }

    /* =======================
       Ventas por día (últimos 30)
       ======================= */
    public function daily(Request $request)
    {
        $from = Carbon::now()->startOfDay()->subDays(29);
        $to = Carbon::now()->endOfDay();

        $rows = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("date_trunc('day', paid_at)::date as d, COUNT(*) as orders, SUM(total) as revenue")
            ->groupByRaw("date_trunc('day', paid_at)")
            ->orderBy('d')
            ->get();

        // Relleno de días faltantes
        $dates = collect();
        for ($i = 0; $i < 30; $i++) {
            $dates->push($from->copy()->addDays($i)->toDateString());
        }

        $data = $dates->map(function ($d) use ($rows) {
            $r = $rows->firstWhere('d', $d);

            return [
                'date' => $d,
                'orders' => (int) ($r->orders ?? 0),
                'revenue' => (float) ($r->revenue ?? 0.0),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /* =======================
       Ventas por semana (últimas 12)
       ======================= */
    public function weekly(Request $request)
    {
        // Semanas ISO: comienzan en lunes en PostgreSQL con date_trunc('week', ...)
        $from = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(11);
        $to = Carbon::now()->endOfWeek(Carbon::MONDAY);

        $rows = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("date_trunc('week', paid_at)::date as week_start, COUNT(*) as orders, SUM(total) as revenue")
            ->groupByRaw("date_trunc('week', paid_at)")
            ->orderBy('week_start')
            ->get();

        $data = $rows->map(fn ($r) => [
            'week' => Carbon::parse($r->week_start)->toDateString(), // lunes de esa semana
            'orders' => (int) $r->orders,
            'revenue' => (float) $r->revenue,
        ]);

        return response()->json(['data' => $data]);
    }

    /* =======================
       Ventas por mes (últimos 12)
       ======================= */
    public function monthly(Request $request)
    {
        $from = Carbon::now()->startOfMonth()->subMonths(11);
        $to = Carbon::now()->endOfMonth();

        $rows = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("to_char(paid_at, 'YYYY-MM') as ym, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Relleno de meses faltantes (YYYY-MM)
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $months->push($from->copy()->addMonths($i)->format('Y-m'));
        }

        $data = $months->map(function ($m) use ($rows) {
            $r = $rows->firstWhere('ym', $m);

            return [
                'month' => $m,
                'orders' => (int) ($r->orders ?? 0),
                'revenue' => (float) ($r->revenue ?? 0.0),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /* =======================
       Top productos (día/semana/mes)
       ======================= */
    public function topProducts(Request $request)
    {
        $range = $request->query('range', 'month'); // day|week|month
        $now = Carbon::now();

        [$from, $to] = match ($range) {
            'day' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        // detalle_json es el snapshot preferido, pero pedidos históricos pueden no tenerlo.
        // Cargamos también las relaciones normalizadas para poder construir el top en esos casos.
        $pedidos = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->with([
                'detalles.producto:id,nombre,precio',
                'detalles.sabor:id,nombre',
                'detalles.tamano:id,nombre',
                'detalles.masa:id,tipo',
                'productos:id,nombre,precio',
                'promociones.promocion:id,nombre',
            ])
            ->get();

        $map = [];

        foreach ($pedidos as $pedido) {
            $items = $pedido->detalle_json['items'] ?? [];

            // Pedidos nuevos: el snapshot conserva mejor el nombre y el total exacto de cada línea.
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    $qty = max(1, (int) ($item['cantidad'] ?? 1));
                    $name = ($item['tipo'] ?? '') === 'promocion'
                        ? 'Promoción: '.($item['nombre'] ?? 'Sin nombre')
                        : trim(
                            ($item['nombre'] ?? 'Producto').' '.
                            ($item['tamano'] ?? '').' '.
                            ($item['sabor'] ?? '').' '.
                            ($item['masa'] ?? $item['masa_nombre'] ?? '')
                        );
                    $name = preg_replace('/\s+/', ' ', $name) ?: 'Producto';

                    $lineRevenue = isset($item['precio_total'])
                        ? (float) $item['precio_total']
                        : (float) ($item['precio'] ?? 0) * $qty;

                    $this->accumulateTopProduct($map, $name, $qty, $lineRevenue);
                }

                continue;
            }

            $hasNormalizedDetails = false;

            // Fallback para pedidos históricos con detalle_pedidos pero sin detalle_json.
            foreach ($pedido->detalles as $detalle) {
                $hasNormalizedDetails = true;
                $qty = max(1, (int) ($detalle->cantidad ?? 1));
                $name = trim((string) ($detalle->producto?->nombre ?? ''));

                if ($name === '') {
                    $name = trim(implode(' ', array_filter([
                        $detalle->sabor?->nombre,
                        $detalle->tamano?->nombre,
                        $detalle->masa?->tipo,
                    ])));
                }

                $this->accumulateTopProduct(
                    $map,
                    $name !== '' ? $name : 'Producto',
                    $qty,
                    (float) ($detalle->precio_total ?? 0)
                );
            }

            // Las promociones se muestran como una línea de promoción, igual que en el snapshot.
            if ($pedido->promociones->isNotEmpty()) {
                $hasNormalizedDetails = true;

                foreach ($pedido->promociones->groupBy('promocion_id') as $grupo) {
                    $primero = $grupo->first();
                    $name = 'Promoción: '.($primero?->promocion?->nombre ?? 'Sin nombre');
                    $revenue = (float) $grupo->sum('precio_total');
                    $qty = max(1, $grupo->filter(fn ($detalle) => (float) ($detalle->precio_total ?? 0) > 0)->count());

                    $this->accumulateTopProduct($map, $name, $qty, $revenue);
                }
            }

            if ($hasNormalizedDetails) {
                continue;
            }

            // Último fallback para pedidos antiguos que solo conservan pedido_producto.
            foreach ($pedido->productos as $producto) {
                $qty = max(1, (int) ($producto->pivot->cantidad ?? 1));
                $this->accumulateTopProduct(
                    $map,
                    $producto->nombre ?: 'Producto',
                    $qty,
                    (float) ($producto->precio ?? 0) * $qty
                );
            }
        }

        $top = collect($map)
            ->sortByDesc('qty')
            ->take(10)
            ->values()
            ->all();

        return response()->json([
            'data' => $top,
            'meta' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'range' => $range,
            ],
        ]);
    }

    private function accumulateTopProduct(array &$map, string $name, int $qty, float $revenue): void
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);
        $name = $name !== '' ? $name : 'Producto';

        if (! isset($map[$name])) {
            $map[$name] = [
                'name' => $name,
                'qty' => 0,
                'revenue' => 0.0,
            ];
        }

        $map[$name]['qty'] += max(1, $qty);
        $map[$name]['revenue'] += $revenue;
    }
}
