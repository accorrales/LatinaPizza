@extends('layouts.app')

@section('meta_description', 'Dashboard de ventas y analítica de Latina Pizza.')

@section('content')
<div
    x-data="salesDashboard"
    data-api-url="{{ url('/admin/ventas/api') }}"
    class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 min-h-[75vh] bg-[#f4f7fb] text-slate-950"
>
    <div class="mx-auto max-w-[1360px] px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
        <header class="mb-7 rounded-[30px] bg-[#071426] px-5 py-6 text-white shadow-[0_24px_70px_rgba(7,20,38,0.16)] sm:px-7 lg:px-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-200">
                        <i class="fas fa-chart-line"></i>
                        Business intelligence
                    </span>
                    <h1 class="mt-4 text-3xl font-bold tracking-[-0.04em] sm:text-4xl lg:text-5xl">Ventas & rendimiento</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">
                        Ingresos, pedidos y productos con mayor movimiento. <span class="text-slate-400">Actualizado: <span x-text="lastUpdated"></span></span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <label class="inline-flex h-12 items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-4 text-xs font-semibold text-slate-200">
                        <input type="checkbox" class="rounded border-white/20 bg-transparent text-blue-500 focus:ring-blue-400" x-model="autoRefresh" @change="toggleAutoRefresh()">
                        Auto-refresh
                        <span class="text-slate-500">30s</span>
                    </label>
                    <button type="button" @click="reloadAll(true)" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-white px-4 text-sm font-bold text-[#071426] transition hover:-translate-y-0.5 hover:bg-blue-50">
                        <i class="fas fa-rotate-right text-xs text-blue-600"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </header>

        <section class="mb-6 rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-[220px_220px_minmax(0,1fr)] lg:items-end">
                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Tipo de pedido</label>
                    <select class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" x-model="filters.tipo_pedido" @change="reloadAll(true)">
                        <option value="">Todos</option>
                        <option value="express">Express</option>
                        <option value="pickup">Pickup</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Sucursal (ID)</label>
                    <input type="number" min="1" placeholder="Ej: 1" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" x-model.number="filters.sucursal_id" @change="reloadAll(true)">
                </div>
                <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <i class="fas fa-circle-info mr-2 text-blue-600"></i>
                    Las métricas muestran 30 días, 12 semanas y 12 meses según el gráfico.
                </div>
            </div>
        </section>

        <section class="mb-6 grid gap-4 md:grid-cols-3" aria-label="Indicadores de ventas">
            <article class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Hoy</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-[#071426]" x-text="kpi.todayRevenueFmt"></p>
                        <p class="mt-2 text-sm text-slate-500"><span class="font-bold text-slate-700" x-text="kpi.todayOrders"></span> pedidos</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600"><i class="fas fa-sack-dollar"></i></span>
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Esta semana</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-[#071426]" x-text="kpi.weekRevenueFmt"></p>
                        <p class="mt-2 text-sm text-slate-500"><span class="font-bold text-slate-700" x-text="kpi.weekOrders"></span> pedidos</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><i class="fas fa-calendar-week"></i></span>
                </div>
            </article>

            <article class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Este mes</p>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-[#071426]" x-text="kpi.monthRevenueFmt"></p>
                        <p class="mt-2 text-sm text-slate-500"><span class="font-bold text-slate-700" x-text="kpi.monthOrders"></span> pedidos</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><i class="fas fa-chart-column"></i></span>
                </div>
            </article>
        </section>

        <section class="mb-6 grid gap-5 xl:grid-cols-2">
            @foreach([['daily','chartDaily','Ventas por día','Últimos 30 días'], ['weekly','chartWeekly','Ventas por semana','Últimas 12 semanas']] as [$loadingKey, $chartId, $title, $subtitle])
                <article class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.05)] sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-[#071426]">{{ $title }}</h2>
                            <p class="mt-1 text-xs text-slate-400">{{ $subtitle }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-chart-line"></i></span>
                    </div>
                    <div class="relative h-[280px]">
                        <canvas id="{{ $chartId }}" class="absolute inset-0"></canvas>
                        <div x-show="loading.{{ $loadingKey }}" class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/80 backdrop-blur-sm">
                            <div class="h-7 w-7 animate-spin rounded-full border-2 border-blue-100 border-t-blue-600"></div>
                        </div>
                    </div>
                </article>
            @endforeach

            <article class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.05)] sm:p-6 xl:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-[#071426]">Ventas por mes</h2>
                        <p class="mt-1 text-xs text-slate-400">Últimos 12 meses</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"><i class="fas fa-chart-area"></i></span>
                </div>
                <div class="relative h-[320px]">
                    <canvas id="chartMonthly" class="absolute inset-0"></canvas>
                    <div x-show="loading.monthly" class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/80 backdrop-blur-sm">
                        <div class="h-7 w-7 animate-spin rounded-full border-2 border-blue-100 border-t-blue-600"></div>
                    </div>
                </div>
            </article>
        </section>

        <section class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-[0_12px_38px_rgba(7,20,38,0.05)] sm:p-6">
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Rendimiento de catálogo</p>
                    <h2 class="mt-1 text-xl font-bold text-[#071426]">Top productos</h2>
                    <p class="mt-1 text-sm text-slate-500">Qué productos están moviendo más unidades e ingresos.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="inline-flex overflow-hidden rounded-full bg-slate-100 p-1">
                        <button type="button" class="rounded-full px-4 py-2 text-xs font-bold transition" :class="prodRange === 'day' ? 'bg-[#071426] text-white' : 'text-slate-500'" @click="setProductRangeDay">Hoy</button>
                        <button type="button" class="rounded-full px-4 py-2 text-xs font-bold transition" :class="prodRange === 'week' ? 'bg-[#071426] text-white' : 'text-slate-500'" @click="setProductRangeWeek">Semana</button>
                        <button type="button" class="rounded-full px-4 py-2 text-xs font-bold transition" :class="prodRange === 'month' ? 'bg-[#071426] text-white' : 'text-slate-500'" @click="setProductRangeMonth">Mes</button>
                    </div>
                    <button type="button" @click="exportTopCsv" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-blue-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50">
                        <i class="fas fa-file-csv"></i>
                        Exportar CSV
                    </button>
                </div>
            </div>

            <template x-if="loading.top"><div class="h-32 animate-pulse rounded-2xl bg-slate-100"></div></template>

            <div x-show="!loading.top" class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full min-w-[680px] text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="w-64 px-4 py-3">Cantidad</th>
                            <th class="px-4 py-3 text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in topProducts" :key="row.name">
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-4 py-4 font-semibold text-[#071426]">
                                    <div class="flex items-center gap-3"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span><span x-text="row.name"></span></div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-red-500" :style="{ width: barWidth(row.qty) }"></div></div>
                                    <p class="mt-1.5 text-xs font-medium text-slate-400"><span x-text="row.qty"></span> unidades</p>
                                </td>
                                <td class="px-4 py-4 text-right font-bold text-slate-700" x-text="money(row.revenue)"></td>
                            </tr>
                        </template>
                        <tr x-show="topProducts.length === 0"><td colspan="3" class="px-4 py-10 text-center text-sm text-slate-400">Sin datos para el rango seleccionado.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
