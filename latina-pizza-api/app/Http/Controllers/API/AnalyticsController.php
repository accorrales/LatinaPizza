<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /* =======================
       Helpers base de consulta
       ======================= */
    protected function baseQuery(Request $request)
    {
        $q = Pedido::query()->whereNotNull('paid_at'); // o ->where('payment_status','paid')

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
        $to   = Carbon::now()->endOfDay();

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
                'date'    => $d,
                'orders'  => (int) ($r->orders ?? 0),
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
        $to   = Carbon::now()->endOfWeek(Carbon::MONDAY);

        $rows = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("date_trunc('week', paid_at)::date as week_start, COUNT(*) as orders, SUM(total) as revenue")
            ->groupByRaw("date_trunc('week', paid_at)")
            ->orderBy('week_start')
            ->get();

        $data = $rows->map(fn($r) => [
            'week'    => Carbon::parse($r->week_start)->toDateString(), // lunes de esa semana
            'orders'  => (int) $r->orders,
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
        $to   = Carbon::now()->endOfMonth();

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
                'month'   => $m,
                'orders'  => (int) ($r->orders ?? 0),
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
            'day'  => [$now->copy()->startOfDay(),  $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $pedidos = $this->baseQuery($request)
            ->whereBetween('paid_at', [$from, $to])
            ->select(['detalle_json'])
            ->get();

        $map = []; // nombre => ['name'=>..., 'qty'=>..., 'revenue'=>...]
        foreach ($pedidos as $p) {
            $det = $p->detalle_json ?? [];
            foreach (($det['items'] ?? []) as $it) {
                if (($it['tipo'] ?? '') !== 'producto') continue;

                $qty  = (int) ($it['cantidad'] ?? 1);
                $name = trim(
                    ($it['nombre'] ?? 'Producto') . ' ' .
                    ($it['tamano'] ?? '') . ' ' .
                    ($it['sabor'] ?? '') . ' ' .
                    ($it['masa_nombre'] ?? '')
                );
                $name = preg_replace('/\s+/', ' ', $name);

                $lineRevenue = 0.0;
                if (isset($it['precio_total'])) {
                    $lineRevenue = (float) $it['precio_total'];
                } elseif (isset($it['precio'])) {
                    $lineRevenue = (float) $it['precio'] * $qty;
                }

                if (!isset($map[$name])) {
                    $map[$name] = ['name' => $name, 'qty' => 0, 'revenue' => 0.0];
                }
                $map[$name]['qty']     += $qty;
                $map[$name]['revenue'] += $lineRevenue;
            }
        }

        $top = collect($map)->sortByDesc('qty')->take(10)->values()->all();

        return response()->json([
            'data' => $top,
            'meta' => [
                'from'  => $from->toDateString(),
                'to'    => $to->toDateString(),
                'range' => $range
            ]
        ]);
    }
}

