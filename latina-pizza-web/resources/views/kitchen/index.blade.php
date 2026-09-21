@extends('layouts.app')

@section('content')
<div x-data="kitchenPanel" data-api-url="{{ url('/kitchen/api') }}" class="px-4">
  <div class="flex flex-wrap items-center gap-3 mb-5">
    <div class="inline-flex rounded overflow-hidden border">
      <button type="button" :class="tabBtn('nuevo')" @click="setStatus('nuevo')">Nuevo <span class="ml-1" x-text="counts.nuevo"></span></button>
      <button type="button" :class="tabBtn('preparacion')" @click="setStatus('preparacion')">Preparación <span class="ml-1" x-text="counts.preparacion"></span></button>
      <button type="button" :class="tabBtn('listo')" @click="setStatus('listo')">Listo <span class="ml-1" x-text="counts.listo"></span></button>
    </div>

    <select class="border rounded px-2 py-1" x-model="filters.tipo_pedido" @change="fetchOrders()">
      <option value="">Todos</option><option value="pickup">Pickup</option><option value="express">Express</option>
    </select>

    <input type="text" class="border rounded px-3 py-1" placeholder="Buscar #ID o Cliente" x-model.lazy="filters.search" @change="fetchOrders()">
    <select class="border rounded px-2 py-1" x-model.number="filters.limit" @change="fetchOrders()">
      <option>20</option><option selected>50</option><option>100</option>
    </select>

    <div class="ml-auto text-sm text-gray-500">Hora servidor: <span x-text="meta.server_time"></span></div>
  </div>

  <div x-show="errMsg" x-cloak class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="errMsg"></div>

  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <template x-for="o in orders" :key="o.id">
      <div class="bg-white rounded-xl border shadow-sm p-4 flex flex-col gap-2"
           :class="{ 'ring-2 ring-red-500': o.over_sla > 0, 'ring-2 ring-amber-400': o.priority }">
        <div class="flex items-center justify-between">
          <div class="font-semibold">#<span x-text="o.id"></span> · <span x-text="o.cliente"></span></div>
          <div class="text-xs">
            <span class="px-2 py-0.5 rounded-full border"
                  :class="o.tipo_pedido === 'express' ? 'border-blue-500 text-blue-600' : 'border-red-500 text-red-600'"
                  x-text="o.tipo_pedido === 'express' ? 'Express' : 'Pickup'"></span>
          </div>
        </div>

        <div class="text-sm text-gray-600">
          ₡<span x-text="o.total.toFixed(0)"></span> · Espera: <span x-text="o.mins_waiting"></span> min
          <template x-if="o.sla_minutes"><span> · SLA: <span x-text="o.sla_minutes"></span>m</span></template>
          <template x-if="o.over_sla > 0"><span> · <span class="text-red-600 font-semibold">+<span x-text="o.over_sla"></span>m</span></span></template>
        </div>

        <ul class="text-sm list-disc pl-5">
          <template x-for="it in o.items">
            <li>
              <div x-text="it.texto"></div>
              <template x-if="it.detalle && it.detalle.length">
                <ul class="list-disc pl-4 text-gray-600"><template x-for="d in it.detalle"><li x-text="d"></li></template></ul>
              </template>
              <template x-if="it.nota"><div class="italic text-amber-700"><span>Nota: </span><span x-text="it.nota"></span></div></template>
            </li>
          </template>
        </ul>

        <div class="flex flex-wrap gap-2 mt-2">
          <button type="button" class="px-2 py-1 text-xs rounded border hover:bg-gray-50" @click="togglePriority(o)"><span x-text="o.priority ? 'Quitar prioridad' : 'Prioridad'"></span></button>
          <button type="button" class="px-2 py-1 text-xs rounded border hover:bg-gray-50" @click="advance(o)"><span x-text="nextLabel(o.kitchen_status)"></span></button>
          <button type="button" class="px-2 py-1 text-xs rounded border hover:bg-gray-50" @click="markReady(o)" x-show="o.kitchen_status !== 'listo'">Marcar Listo</button>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-2">
          <div class="flex items-center gap-1">
            <input type="number" min="5" max="240" class="border rounded px-2 py-1 text-xs w-20" :value="o.sla_minutes || ''" @change="updateSla(o, $event.target.value)">
            <span class="text-xs text-gray-500">min</span>
          </div>
          <input type="text" class="border rounded px-2 py-1 text-xs" :value="o.notas || ''" placeholder="Nota cocina" @change="updateNotes(o, $event.target.value)">
        </div>
      </div>
    </template>
  </div>

  <div class="flex justify-end mt-6 text-sm text-gray-600" x-show="meta.pagination.total > meta.pagination.per_page">
    Página <span class="mx-1" x-text="meta.pagination.current_page"></span> / <span x-text="meta.pagination.last_page"></span>
  </div>
</div>
@endsection
