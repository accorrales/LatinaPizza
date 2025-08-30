@extends('layouts.app')

@section('content')
<div x-data="kitchenPanel()" x-init="init()" class="px-4">
  <!-- Filtros / KPIs -->
  <div class="flex flex-wrap items-center gap-3 mb-5">
    <div class="inline-flex rounded overflow-hidden border">
      <button :class="tabBtn('nuevo')"        @click="setStatus('nuevo')">Nuevo <span class="ml-1" x-text="counts.nuevo"></span></button>
      <button :class="tabBtn('preparacion')"  @click="setStatus('preparacion')">Preparación <span class="ml-1" x-text="counts.preparacion"></span></button>
      <button :class="tabBtn('listo')"        @click="setStatus('listo')">Listo <span class="ml-1" x-text="counts.listo"></span></button>
    </div>

    <select class="border rounded px-2 py-1" x-model="filters.tipo_pedido" @change="fetchOrders()">
      <option value="">Todos</option>
      <option value="pickup">Pickup</option>
      <option value="express">Express</option>
    </select>

    <input type="text" class="border rounded px-3 py-1" placeholder="Buscar #ID o Cliente" x-model.lazy="filters.search" @change="fetchOrders()">
    <select class="border rounded px-2 py-1" x-model.number="filters.limit" @change="fetchOrders()">
      <option>20</option><option selected>50</option><option>100</option>
    </select>

    <div class="ml-auto text-sm text-gray-500">Hora servidor: <span x-text="meta.server_time"></span></div>
  </div>

  <!-- Grid de pedidos -->
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <template x-for="o in orders" :key="o.id">
      <div class="bg-white rounded-xl border shadow-sm p-4 flex flex-col gap-2"
           :class="{
             'ring-2 ring-red-500': o.over_sla > 0,
             'ring-2 ring-amber-400': o.priority
           }">

        <div class="flex items-center justify-between">
          <div class="font-semibold">#<span x-text="o.id"></span> · <span x-text="o.cliente"></span></div>
          <div class="text-xs">
            <span class="px-2 py-0.5 rounded-full border"
                  :class="o.tipo_pedido==='express' ? 'border-blue-500 text-blue-600' : 'border-red-500 text-red-600'"
                  x-text="o.tipo_pedido==='express' ? 'Express' : 'Pickup'"></span>
          </div>
        </div>

        <div class="text-sm text-gray-600">
          ₡<span x-text="o.total.toFixed(0)"></span> ·
          Espera: <span x-text="o.mins_waiting"></span> min
          <template x-if="o.sla_minutes"> · SLA: <span x-text="o.sla_minutes"></span>m</template>
          <template x-if="o.over_sla > 0"> · <span class="text-red-600 font-semibold">+<span x-text="o.over_sla"></span>m</span></template>
        </div>

        <ul class="text-sm list-disc pl-5">
          <template x-for="it in o.items">
            <li>
              <div x-text="it.texto"></div>
              <template x-if="it.detalle && it.detalle.length">
                <ul class="list-disc pl-4 text-gray-600">
                  <template x-for="d in it.detalle"><li x-text="d"></li></template>
                </ul>
              </template>
              <template x-if="it.nota"><div class="italic text-amber-700" x-text="'Nota: '+it.nota"></div></template>
            </li>
          </template>
        </ul>

        <!-- Acciones -->
        <div class="flex flex-wrap gap-2 mt-2">
          <button class="px-2 py-1 text-xs rounded border hover:bg-gray-50"
                  @click="togglePriority(o)"><span x-text="o.priority ? 'Quitar prioridad' : 'Prioridad'"></span></button>

          <button class="px-2 py-1 text-xs rounded border hover:bg-gray-50"
                  @click="advance(o)"><span x-text="nextLabel(o.kitchen_status)"></span></button>

          <button class="px-2 py-1 text-xs rounded border hover:bg-gray-50"
                  @click="markReady(o)" x-show="o.kitchen_status!=='listo'">Marcar Listo</button>
        </div>

        <!-- SLA / Nota -->
        <div class="grid grid-cols-2 gap-2 mt-2">
          <div class="flex items-center gap-1">
            <input type="number" min="5" max="240" class="border rounded px-2 py-1 text-xs w-20"
                   :value="o.sla_minutes || ''" @change="e=>updateSla(o, e.target.value)">
            <span class="text-xs text-gray-500">min</span>
          </div>
          <input type="text" class="border rounded px-2 py-1 text-xs"
                 :value="o.notas || ''" placeholder="Nota cocina"
                 @change="e=>updateNotes(o, e.target.value)">
        </div>
      </div>
    </template>
  </div>

  <!-- Paginación simple -->
  <div class="flex justify-end mt-6 text-sm text-gray-600" x-show="meta.pagination.total > meta.pagination.per_page">
    Página <span class="mx-1" x-text="meta.pagination.current_page"></span> / <span x-text="meta.pagination.last_page"></span>
  </div>
</div>

@push('scripts')
<script>
function kitchenPanel(){
  // Debe terminar en /api
  const API_BASE = "{{ rtrim(config('services.latina_api.base_url'), '/') }}";
  const TOKEN    = (localStorage.getItem('token') || @json(session('token') ?? '')).toString().trim();

  const authHeaders = (json = true) => {
    const h = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    if (json) h['Content-Type'] = 'application/json';
    if (TOKEN) h['Authorization'] = `Bearer ${TOKEN}`;
    return h;
  };

  // --- helper de error (existía como this._asErr; ahora función local) ---
  async function asErr(res){
    let msg = `HTTP ${res.status}`;
    try { const j = await res.json(); if (j?.message) msg = j.message; } catch {}
    return new Error(msg);
  }

  async function getJson(path) {
    const res = await fetch(`${API_BASE}${path}`, {
      method: 'GET',
      mode: 'cors',
      credentials: 'omit',
      headers: authHeaders(false),
    });
    if (!res.ok) throw await asErr(res);
    return res.json();
  }

  async function patchJson(path, body = {}) {
    const res = await fetch(`${API_BASE}${path}`, {
      method: 'PATCH',
      mode: 'cors',
      credentials: 'omit',     // <- para NO mandar cookies
      headers: authHeaders(true),
      body: JSON.stringify(body),
    });
    if (!res.ok) throw await asErr(res);
    return res.json();
  }

  return {
    // state
    orders: [],
    counts: { nuevo: 0, preparacion: 0, listo: 0 },
    meta:   { server_time:'', pagination:{ current_page:1, per_page:50, total:0, last_page:1 } },
    filters:{ status:'nuevo', tipo_pedido:'', search:'', limit:50 },
    timer:  null,
    loading:false,
    errMsg:'',

    // helpers UI
    tabBtn(s){ return 'px-3 py-1 text-sm rounded ' + (this.filters.status===s ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'); },
    setStatus(s){ this.filters.status = s; this.fetchOrders(true); },
    nextLabel(st){ return st==='nuevo' ? '→ Preparación' : (st==='preparacion' ? '→ Listo' : 'Listo'); },

    async init(){
      console.log('API_BASE =', API_BASE); // para verificar que trae /api
      await this.fetchOrders(true);
      this.timer = setInterval(()=>this.fetchOrders(), 6000);
      window.addEventListener('beforeunload', ()=>clearInterval(this.timer));
    },

    async fetchOrders(reset=false){
      this.loading = true; this.errMsg='';
      try{
        const qs = new URLSearchParams({
          status: this.filters.status,
          limit:  String(this.filters.limit || 50),
        });
        if (this.filters.tipo_pedido) qs.set('tipo_pedido', this.filters.tipo_pedido);
        if (this.filters.search)      qs.set('search', this.filters.search);

        const data = await getJson(`/kitchen/orders?${qs}`);
        this.orders = data.data || [];
        this.counts = data.meta?.counts || this.counts;
        this.meta   = data.meta || this.meta;

        if (reset) window.scrollTo({top:0,behavior:'smooth'});
      }catch(e){
        console.error(e);
        this.errMsg = e.message || 'Error al cargar pedidos';
      }finally{
        this.loading = false;
      }
    },

    async updateStatus(o, status){
      await patchJson(`/kitchen/orders/${o.id}/status`, { status });
      await this.fetchOrders();
    },

    async advance(o){
      const next = o.kitchen_status === 'nuevo' ? 'preparacion' : 'listo';
      await this.updateStatus(o, next);
    },

    async markReady(o){
      await patchJson(`/kitchen/orders/${o.id}/ready`);
      await this.fetchOrders();
    },

    async togglePriority(o){
      await patchJson(`/kitchen/orders/${o.id}/priority`, { priority: !o.priority });
      await this.fetchOrders();
    },

    async updateSla(o, minutes){
      const m = parseInt(minutes,10);
      if (isNaN(m) || m < 5 || m > 240) return;
      await patchJson(`/kitchen/orders/${o.id}/sla`, { sla_minutes: m });
      await this.fetchOrders();
    },

    async updateNotes(o, notes){
      await patchJson(`/kitchen/orders/${o.id}/notes`, { notes });
      const target = this.orders.find(x => x.id === o.id);
      if (target) target.notas = notes;
    },
  }
}
</script>
@endpush
@endsection
