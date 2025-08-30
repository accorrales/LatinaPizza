@extends('layouts.app')

@section('content')
<div x-data="ventasDashboard()" x-init="init()" class="space-y-8 p-4">

  <!-- Header + Filtros -->
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">📈 Dashboard de Ventas</h1>
      <p class="text-sm text-gray-500">Últimos 30 días, 12 semanas y 12 meses. <span class="ml-1 text-xs" x-text="'Actualizado: ' + lastUpdated"></span></p>
    </div>

    <div class="flex flex-col md:flex-row gap-3 md:items-end">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tipo de pedido</label>
          <select class="border rounded-xl px-3 py-2 text-sm w-full focus:ring-2 focus:ring-rose-300"
                  x-model="filters.tipo_pedido" @change="reloadAll(true)">
            <option value="">Todos</option>
            <option value="express">Express</option>
            <option value="pickup">Pickup</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Sucursal (ID)</label>
          <input type="number" min="1" placeholder="Ej: 1"
                 class="border rounded-xl px-3 py-2 text-sm w-full focus:ring-2 focus:ring-rose-300"
                 x-model.number="filters.sucursal_id" @change="reloadAll(true)">
        </div>
      </div>

      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 text-sm px-3 py-2 rounded-full border bg-white">
          <input type="checkbox" class="rounded" x-model="autoRefresh" @change="toggleAutoRefresh()">
          Auto-refresh <span class="text-xs text-gray-500">(30s)</span>
        </label>
        <button @click="reloadAll(true)"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-full border bg-white hover:bg-gray-50 text-sm shadow-sm">
          <i class="fa-solid fa-rotate-right"></i> Actualizar
        </button>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="grid sm:grid-cols-3 gap-4">
    <!-- Hoy -->
    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-rose-400 via-orange-300 to-amber-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Hoy</p>
            <p class="text-2xl font-semibold" x-text="kpi.todayRevenueFmt"></p>
          </div>
          <div class="text-rose-500/90 text-xl"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.todayOrders"></span> pedidos</p>
      </div>
    </div>

    <!-- Semana -->
    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-sky-400 via-indigo-300 to-fuchsia-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Semana</p>
            <p class="text-2xl font-semibold" x-text="kpi.weekRevenueFmt"></p>
          </div>
          <div class="text-sky-500/90 text-xl"><i class="fa-solid fa-calendar-week"></i></div>
        </div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.weekOrders"></span> pedidos</p>
      </div>
    </div>

    <!-- Mes -->
    <div class="group relative p-[1px] rounded-2xl bg-gradient-to-r from-emerald-400 via-lime-300 to-amber-300">
      <div class="bg-white rounded-2xl p-4 h-full shadow-sm">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-500">Mes</p>
            <p class="text-2xl font-semibold" x-text="kpi.monthRevenueFmt"></p>
          </div>
          <div class="text-emerald-500/90 text-xl"><i class="fa-solid fa-chart-column"></i></div>
        </div>
        <p class="mt-1 text-sm text-gray-500"><span class="font-semibold" x-text="kpi.monthOrders"></span> pedidos</p>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="grid xl:grid-cols-2 gap-6">
    <!-- Diario -->
    <div class="rounded-2xl border shadow-sm bg-white p-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-medium">Ventas por día (últimos 30)</h2>
      </div>
      <div class="relative h-[260px]">
        <canvas id="chartDaily" class="absolute inset-0"></canvas>
        <div x-show="loading.daily" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center">
          <div class="h-6 w-6 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    </div>

    <!-- Semanal -->
    <div class="rounded-2xl border shadow-sm bg-white p-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-medium">Ventas por semana (últimas 12)</h2>
      </div>
      <div class="relative h-[260px]">
        <canvas id="chartWeekly" class="absolute inset-0"></canvas>
        <div x-show="loading.weekly" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center">
          <div class="h-6 w-6 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    </div>

    <!-- Mensual -->
    <div class="rounded-2xl border shadow-sm bg-white p-4 xl:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-medium">Ventas por mes (últimos 12)</h2>
      </div>
      <div class="relative h-[300px]">
        <canvas id="chartMonthly" class="absolute inset-0"></canvas>
        <div x-show="loading.monthly" class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center">
          <div class="h-6 w-6 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Top productos -->
  <div class="bg-white rounded-2xl border p-4 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
      <div class="flex items-center gap-3">
        <h2 class="font-medium">Top productos</h2>
        <div class="inline-flex rounded-full border overflow-hidden">
          <button class="px-3 py-1 text-sm" :class="prodRange==='day' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="prodRange='day'; loadTopProducts()">Hoy</button>
          <button class="px-3 py-1 text-sm" :class="prodRange==='week' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="prodRange='week'; loadTopProducts()">Semana</button>
          <button class="px-3 py-1 text-sm" :class="prodRange==='month' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" @click="prodRange='month'; loadTopProducts()">Mes</button>
        </div>
      </div>
      <button @click="exportTopCSV()" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border bg-white hover:bg-gray-50 text-sm shadow-sm">
        <i class="fa-solid fa-file-csv"></i> Exportar CSV
      </button>
    </div>

    <template x-if="loading.top">
      <div class="h-24 rounded-xl bg-gray-100 animate-pulse"></div>
    </template>

    <div x-show="!loading.top" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left border-b bg-gray-50">
            <th class="py-2 px-2">Producto</th>
            <th class="py-2 px-2 w-48">Cantidad</th>
            <th class="py-2 px-2">Ingresos</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="row in topProducts" :key="row.name">
            <tr class="border-b last:border-0">
              <td class="py-2 px-2">
                <div class="flex items-center gap-2">
                  <span class="inline-flex h-2 w-2 rounded-full bg-rose-400"></span>
                  <span x-text="row.name"></span>
                </div>
              </td>
              <td class="py-2 px-2">
                <div class="w-full bg-gray-100 rounded-full h-2">
                  <div class="h-2 rounded-full bg-gradient-to-r from-rose-500 to-amber-400"
                       :style="{ width: (Math.max(1, row.qty) / Math.max(1, maxQty) * 100) + '%' }"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1" x-text="row.qty + ' uds'"></div>
              </td>
              <td class="py-2 px-2" x-text="money(row.revenue)"></td>
            </tr>
          </template>
          <tr x-show="topProducts.length===0">
            <td colspan="3" class="py-8 text-center text-gray-500">Sin datos para el rango seleccionado.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function ventasDashboard(){
  const API_BASE = "{{ rtrim($apiBase ?? config('services.latina_api.base_url'), '/') }}";
  const API = API_BASE + '/analytics';
  const TOKEN = (`{{ $apiToken ?? '' }}` || localStorage.getItem('token') || '').trim();

  const headers = {
    'Accept':'application/json',
    'X-Requested-With':'XMLHttpRequest',
    ...(TOKEN ? { 'Authorization': `Bearer ${TOKEN}` } : {})
  };

  // ----- Chart theme -----
  Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Noto Sans, Ubuntu, Cantarell';
  Chart.defaults.color = '#475569'; // slate-600
  const GRID = '#E5E7EB';           // gray-200
  const COLORS = {
    orders:  'rgb(59,130,246)',     // sky-500
    revenue: 'rgb(244,63,94)'       // rose-500
  };

  const moneyFmt = new Intl.NumberFormat('es-CR',{style:'currency',currency:'CRC',maximumFractionDigits:0});
  const fmtDateTime = (d)=> new Intl.DateTimeFormat('es-CR',{dateStyle:'medium',timeStyle:'short'}).format(d);

  const baseOpts = {
    responsive:true,
    maintainAspectRatio:false,
    animation:false,
    resizeDelay: 150,
    interaction:{ mode:'index', intersect:false },
    plugins:{ legend:{ display:true, position:'bottom' } },
    elements:{ point:{ radius:0, hitRadius:6, hoverRadius:4 } },
    scales:{
      x:{ grid:{ color: GRID, drawTicks:false }},
      y:{ grid:{ color: GRID }, beginAtZero:true, ticks:{ precision:0 }},
      y1:{ grid:{ drawOnChartArea:false }, beginAtZero:true, position:'right' }
    }
  };

  const lineCfg = (labels, orders, revenue, ctx) => {
    // Gradiente de relleno para ingresos
    const grad = ctx.createLinearGradient(0,0,0,220);
    grad.addColorStop(0, 'rgba(244,63,94,0.30)');
    grad.addColorStop(1, 'rgba(244,63,94,0.04)');

    return {
      type:'line',
      data:{
        labels,
        datasets:[
          { label:'# Pedidos', data:orders, borderWidth:2, tension:0.3, borderColor:COLORS.orders, backgroundColor:'transparent' },
          { label:'Ingresos', data:revenue, yAxisID:'y1', borderWidth:2, tension:0.3, borderColor:COLORS.revenue, fill:true, backgroundColor:grad }
        ]
      },
      options:{
        ...baseOpts,
        plugins:{ 
          ...baseOpts.plugins,
          tooltip:{ callbacks:{
            label:(ctx)=> ctx.dataset.label === 'Ingresos'
              ? `${ctx.dataset.label}: ${moneyFmt.format(ctx.parsed.y)}`
              : `${ctx.dataset.label}: ${ctx.parsed.y}`
          }}
        }
      }
    }
  };

  // Instancias únicas
  let charts = { daily:null, weekly:null, monthly:null };
  const ensureChart = (key, canvasId, labels, orders, revenue) => {
    const cv = document.getElementById(canvasId);
    const ctx = cv.getContext('2d');
    if (!charts[key]) {
      charts[key] = new Chart(ctx, lineCfg(labels, orders, revenue, ctx));
    } else {
      setChartData(charts[key], labels, orders, revenue);
    }
  };

  const setChartData = (chart, labels, orders, revenue) => {
    chart.data.labels = labels;
    chart.data.datasets[0].data = orders;
    chart.data.datasets[1].data = revenue;
    chart.update('none');
  };

  let timer = null;
  let fetching = false;
  let abortCtl = null;

  return {
    filters: { tipo_pedido:'', sucursal_id:'' },
    prodRange: 'month',
    topProducts: [],
    maxQty: 1,
    kpi: { todayRevenueFmt:'₡0', todayOrders:0, weekRevenueFmt:'₡0', weekOrders:0, monthRevenueFmt:'₡0', monthOrders:0 },
    lastUpdated: '-',
    autoRefresh: false,
    loading: { daily:true, weekly:true, monthly:true, top:true },

    money(v){ return moneyFmt.format(v||0); },

    paramsQS(){
      const q = new URLSearchParams();
      if (this.filters.tipo_pedido) q.set('tipo_pedido', this.filters.tipo_pedido);
      if (this.filters.sucursal_id) q.set('sucursal_id', this.filters.sucursal_id);
      return q.toString();
    },

    async init(){
      if(!TOKEN) console.warn('Sin TOKEN: usa session("token") o localStorage.setItem("token","...")');
      await this.reloadAll(true);
    },

    async reloadAll(showLoading=false){
      if (fetching) return;
      fetching = true;
      if (showLoading) this.loading = { daily:true, weekly:true, monthly:true, top:true };

      if (abortCtl) abortCtl.abort();
      abortCtl = new AbortController();

      try{
        const qs = this.paramsQS();
        await Promise.all([
          this._loadDaily(qs, abortCtl.signal),
          this._loadWeekly(qs, abortCtl.signal),
          this._loadMonthly(qs, abortCtl.signal),
          this._loadTopProducts(qs, abortCtl.signal),
        ]);
        this.computeKPIs();
        this.lastUpdated = fmtDateTime(new Date());
      } catch(e){
        if (e.name !== 'AbortError') console.error(e);
      } finally {
        fetching = false;
      }
    },

    async _loadDaily(qs, signal){
      const res = await fetch(`${API}/sales/daily${qs?`?${qs}`:''}`, { headers, mode:'cors', credentials:'omit', signal });
      if(!res.ok) return this.handleHttpError(res,'daily');
      const j = await res.json();
      const labels = j.data.map(r=>r.date);
      const orders = j.data.map(r=>r.orders);
      const revenue= j.data.map(r=>r.revenue);
      ensureChart('daily','chartDaily',labels,orders,revenue);
      this._dailyCache = { labels, orders, revenue };
      this.loading.daily = false;
    },

    async _loadWeekly(qs, signal){
      const res = await fetch(`${API}/sales/weekly${qs?`?${qs}`:''}`, { headers, mode:'cors', credentials:'omit', signal });
      if(!res.ok) return this.handleHttpError(res,'weekly');
      const j = await res.json();
      const labels = j.data.map(r=>r.week);
      const orders = j.data.map(r=>r.orders);
      const revenue= j.data.map(r=>r.revenue);
      ensureChart('weekly','chartWeekly',labels,orders,revenue);
      this._weeklyCache = { labels, orders, revenue };
      this.loading.weekly = false;
    },

    async _loadMonthly(qs, signal){
      const res = await fetch(`${API}/sales/monthly${qs?`?${qs}`:''}`, { headers, mode:'cors', credentials:'omit', signal });
      if(!res.ok) return this.handleHttpError(res,'monthly');
      const j = await res.json();
      const labels = j.data.map(r=>r.month);
      const orders = j.data.map(r=>r.orders);
      const revenue= j.data.map(r=>r.revenue);
      ensureChart('monthly','chartMonthly',labels,orders,revenue);
      this._monthlyCache = { labels, orders, revenue };
      this.loading.monthly = false;
    },

    async _loadTopProducts(qs, signal){
      const sep = qs ? '&' : '';
      const res = await fetch(`${API}/products/top?range=${this.prodRange}${sep}${qs}`, { headers, mode:'cors', credentials:'omit', signal });
      if(!res.ok) return this.handleHttpError(res,'topProducts');
      const j = await res.json();
      this.topProducts = j.data || [];
      this.maxQty = Math.max(1, ...this.topProducts.map(r => r.qty || 0)); // para barra
      this.loading.top = false;
    },

    computeKPIs(){
      const last = (arr)=> arr?.length ? arr[arr.length-1] : 0;
      this.kpi.todayOrders = last(this._dailyCache?.orders) || 0;
      this.kpi.todayRevenueFmt = moneyFmt.format(last(this._dailyCache?.revenue) || 0);
      this.kpi.weekOrders = last(this._weeklyCache?.orders) || 0;
      this.kpi.weekRevenueFmt = moneyFmt.format(last(this._weeklyCache?.revenue) || 0);
      this.kpi.monthOrders = last(this._monthlyCache?.orders) || 0;
      this.kpi.monthRevenueFmt = moneyFmt.format(last(this._monthlyCache?.revenue) || 0);
    },

    toggleAutoRefresh(){
      if (timer) { clearInterval(timer); timer = null; }
      if (this.autoRefresh) {
        timer = setInterval(()=> this.reloadAll(false), 30000);
        window.addEventListener('beforeunload', ()=> clearInterval(timer), { once:true });
      }
    },

    exportTopCSV(){
      const rows = [['Producto','Cantidad','Ingresos']]
        .concat((this.topProducts||[]).map(r => [r.name, String(r.qty), String(r.revenue)]));
      const csv = rows.map(r => r.map(v => `"${(v??'').toString().replace(/"/g,'""')}"`).join(',')).join('\n');
      const blob = new Blob([csv], { type:'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url; a.download = `top_productos_${this.prodRange}.csv`; a.click();
      URL.revokeObjectURL(url);
    },

    async handleHttpError(res, tag){
      let msg = `Error ${res.status} en ${tag}`;
      try { const j = await res.json(); if(j?.message) msg = j.message; } catch {}
      console.error(msg);
      alert(msg);
    },
  }
}
</script>
@endpush
