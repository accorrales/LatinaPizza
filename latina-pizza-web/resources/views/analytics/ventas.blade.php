@extends('layouts.app')

@section('content')
<div x-data="salesDashboard" data-api-url="{{ url('/admin/ventas/api') }}" class="space-y-8 p-4">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">📈 Dashboard de Ventas</h1>
      <p class="text-sm text-gray-500">Últimos 30 días, 12 semanas y 12 meses. <span class="ml-1 text-xs">Actualizado: <span x-text="lastUpdated"></span></span></p>
    </div>

    <div class="flex flex-col md:flex-row gap-3 md:items-end">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tipo de pedido</label>
          <select class="border rounded-xl px-3 py-2 text-sm w-full focus:ring-2 focus:ring-rose-300" x-model="filters.tipo_pedido" @change="reloadAll(true)">
            <option value="">Todos</option><option value="express">Express</option><option value="pickup">Pickup</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Sucursal (ID)</label>
          <input type="number" min="1" placeholder="Ej: 1" class="border rounded-xl px-3 py-2 text-sm w-full focus:ring-2 focus:ring-rose-300" x-model.number="filters.sucursal_id" @change="reloadAll(true)">
        </div>
      </div>

      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 text-sm px-3 py-2 rounded-full border bg-white">
          <input type="checkbox" class="rounded" x-model="autoRefresh" @change="toggleAutoRefresh()">
          Auto-refresh <span class="text-xs text-gray-500">(30s)</span>
        </label>
        <button type="button" @click="reloadAll(true)" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border bg-white hover:bg-gray-50 text-sm shadow-sm">
          <i class="fa-solid fa-rotate-right"></i> Actualizar
        </button>
      </div>
    </div>
  </div>

  <div class="grid sm:grid-cols-3 gap-4">
    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-rose-400 via-orange-300 to-amber-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between"><div><p class="text-xs uppercase tracking-wide text-gray-500">Hoy</p><p class="text-2xl font-semibold" x-text="kpi.todayRevenueFmt"></p></div><div class="text-rose-500/90 text-xl"><i class="fa-solid fa-sack-dollar"></i></div></div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.todayOrders"></span> pedidos</p>
      </div>
    </div>

    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-sky-400 via-indigo-300 to-fuchsia-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between"><div><p class="text-xs uppercase tracking-wide text-gray-500">Semana</p><p class="text-2xl font-semibold" x-text="kpi.weekRevenueFmt"></p></div><div class="text-sky-500/90 text-xl"><i class="fa-solid fa-calendar-week"></i></div></div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.weekOrders"></span> pedidos</p>
      </div>
    </div>

    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-emerald-400 via-lime-300 to-amber-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between"><div><p class="text-xs uppercase tracking-wide text-gray-500">Mes</p><p class="text-2xl font-semibold" x-text="kpi.monthRevenueFmt"></p></div><div class="text-emerald-500/90 text-xl"><i class="fa-solid fa-chart-column"></i></div></div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.monthOrders"></span> pedidos</p>
      </div>
    </div>
  </div>

  <div class="grid xl:grid-cols-2 gap-6">
    @foreach([['daily','chartDaily','Ventas por día (últimos 30)'], ['weekly','chartWeekly','Ventas por semana (últimas 12)']] as [$loadingKey, $chartId, $title])
      <div class="rounded-2xl border shadow-sm bg-white p-4">
        <h2 class="font-medium mb-2">{{ $title }}</h2>
        <div class="relative h-[260px]">
          <canvas id="{{ $chartId }}" class="absolute inset-0"></canvas>
          <div x-show="loading.{{ $loadingKey }}" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center"><div class="h-6 w-6 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div></div>
        </div>
      </div>
    @endforeach

    <div class="rounded-2xl border shadow-sm bg-white p-4 xl:col-span-2">
      <h2 class="font-medium mb-2">Ventas por mes (últimos 12)</h2>
      <div class="relative h-[300px]">
        <canvas id="chartMonthly" class="absolute inset-0"></canvas>
        <div x-show="loading.monthly" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center"><div class="h-6 w-6 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div></div>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border p-4 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
      <div class="flex items-center gap-3">
        <h2 class="font-medium">Top productos</h2>
        <div class="inline-flex rounded-full border overflow-hidden">
          <button type="button" class="px-3 py-1 text-sm" :class="prodRange === 'day' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="setProductRangeDay">Hoy</button>
          <button type="button" class="px-3 py-1 text-sm" :class="prodRange === 'week' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="setProductRangeWeek">Semana</button>
          <button type="button" class="px-3 py-1 text-sm" :class="prodRange === 'month' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="setProductRangeMonth">Mes</button>
        </div>
      </div>
      <button type="button" @click="exportTopCsv" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border bg-white hover:bg-gray-50 text-sm shadow-sm"><i class="fa-solid fa-file-csv"></i> Exportar CSV</button>
    </div>

    <template x-if="loading.top"><div class="h-24 rounded-xl bg-gray-100 animate-pulse"></div></template>

    <div x-show="!loading.top" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left border-b bg-gray-50"><th class="py-2 px-2">Producto</th><th class="py-2 px-2 w-48">Cantidad</th><th class="py-2 px-2">Ingresos</th></tr></thead>
        <tbody>
          <template x-for="row in topProducts" :key="row.name">
            <tr class="border-b last:border-0">
              <td class="py-2 px-2"><div class="flex items-center gap-2"><span class="inline-flex h-2 w-2 rounded-full bg-rose-400"></span><span x-text="row.name"></span></div></td>
              <td class="py-2 px-2">
                <div class="w-full bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-gradient-to-r from-rose-500 to-amber-400" :style="{ width: barWidth(row.qty) }"></div></div>
                <div class="text-xs text-gray-500 mt-1"><span x-text="row.qty"></span> uds</div>
              </td>
              <td class="py-2 px-2" x-text="money(row.revenue)"></td>
            </tr>
          </template>
          <tr x-show="topProducts.length === 0"><td colspan="3" class="py-8 text-center text-gray-500">Sin datos para el rango seleccionado.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
