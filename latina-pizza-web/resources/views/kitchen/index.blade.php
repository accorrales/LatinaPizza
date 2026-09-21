@extends('layouts.app')

@section('meta_description', 'Panel operativo de cocina de Latina Pizza.')

@section('content')
<div
    x-data="kitchenPanel"
    data-api-url="{{ url('/kitchen/api') }}"
    class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 min-h-[75vh] bg-[#f4f7fb] text-slate-950"
>
    <div class="mx-auto max-w-[1360px] px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
        <header class="mb-7 rounded-[30px] bg-[#071426] px-5 py-6 text-white shadow-[0_24px_70px_rgba(7,20,38,0.16)] sm:px-7 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Operación en vivo
                    </span>
                    <h1 class="mt-4 text-3xl font-bold tracking-[-0.04em] sm:text-4xl lg:text-5xl">Cocina</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">Pedidos activos, tiempos de espera, prioridades y avance de preparación en una sola vista.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Hora servidor</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-white" x-text="meta.server_time"></p>
                    </div>
                    <button type="button" @click="fetchOrders()" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-white px-4 text-sm font-bold text-[#071426] transition hover:-translate-y-0.5 hover:bg-blue-50">
                        <i class="fas fa-rotate-right text-xs text-blue-600"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </header>

        <section class="mb-6 grid gap-3 sm:grid-cols-3" aria-label="Resumen cocina">
            <button type="button" @click="setStatus('nuevo')" class="group rounded-[24px] border border-slate-200 bg-white p-5 text-left shadow-[0_10px_32px_rgba(7,20,38,0.05)] transition hover:-translate-y-0.5 hover:border-red-200">
                <div class="flex items-center justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 text-red-600"><i class="fas fa-bell"></i></span>
                    <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600">Nuevo</span>
                </div>
                <p class="mt-5 text-3xl font-bold tracking-tight text-[#071426]" x-text="counts.nuevo"></p>
                <p class="mt-1 text-sm text-slate-500">Esperando entrar a cocina</p>
            </button>

            <button type="button" @click="setStatus('preparacion')" class="group rounded-[24px] border border-slate-200 bg-white p-5 text-left shadow-[0_10px_32px_rgba(7,20,38,0.05)] transition hover:-translate-y-0.5 hover:border-blue-200">
                <div class="flex items-center justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><i class="fas fa-fire-burner"></i></span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">Preparación</span>
                </div>
                <p class="mt-5 text-3xl font-bold tracking-tight text-[#071426]" x-text="counts.preparacion"></p>
                <p class="mt-1 text-sm text-slate-500">Pedidos actualmente en proceso</p>
            </button>

            <button type="button" @click="setStatus('listo')" class="group rounded-[24px] border border-slate-200 bg-white p-5 text-left shadow-[0_10px_32px_rgba(7,20,38,0.05)] transition hover:-translate-y-0.5 hover:border-emerald-200">
                <div class="flex items-center justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><i class="fas fa-circle-check"></i></span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">Listo</span>
                </div>
                <p class="mt-5 text-3xl font-bold tracking-tight text-[#071426]" x-text="counts.listo"></p>
                <p class="mt-1 text-sm text-slate-500">Terminados y esperando entrega</p>
            </button>
        </section>

        <section class="mb-6 rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center">
                <div class="inline-flex w-full overflow-hidden rounded-2xl bg-slate-100 p-1 sm:w-auto">
                    <button type="button" :class="tabBtn('nuevo')" @click="setStatus('nuevo')">Nuevo <span class="ml-1" x-text="counts.nuevo"></span></button>
                    <button type="button" :class="tabBtn('preparacion')" @click="setStatus('preparacion')">Preparación <span class="ml-1" x-text="counts.preparacion"></span></button>
                    <button type="button" :class="tabBtn('listo')" @click="setStatus('listo')">Listo <span class="ml-1" x-text="counts.listo"></span></button>
                </div>

                <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-[180px_minmax(220px,1fr)_120px]">
                    <select class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" x-model="filters.tipo_pedido" @change="fetchOrders()">
                        <option value="">Todos los tipos</option>
                        <option value="pickup">Pickup</option>
                        <option value="express">Express</option>
                    </select>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-magnifying-glass text-xs"></i></span>
                        <input type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="Buscar #ID o cliente" x-model.lazy="filters.search" @change="fetchOrders()">
                    </div>

                    <select class="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none" x-model.number="filters.limit" @change="fetchOrders()">
                        <option>20</option>
                        <option selected>50</option>
                        <option>100</option>
                    </select>
                </div>
            </div>
        </section>

        <div x-show="errMsg" x-cloak class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" x-text="errMsg"></div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" aria-label="Pedidos de cocina">
            <template x-for="o in orders" :key="o.id">
                <article
                    class="flex min-h-[360px] flex-col overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-[0_14px_44px_rgba(7,20,38,0.07)]"
                    :class="{ 'ring-2 ring-red-500': o.over_sla > 0, 'ring-2 ring-amber-400': o.priority }"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xl font-bold tracking-tight text-[#071426]">#<span x-text="o.id"></span></span>
                                    <span x-show="o.priority" class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">Prioridad</span>
                                    <span x-show="o.over_sla > 0" class="rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-red-700">Fuera SLA</span>
                                </div>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-700" x-text="o.cliente"></p>
                            </div>
                            <span class="rounded-full border px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide"
                                  :class="o.tipo_pedido === 'express' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-red-200 bg-red-50 text-red-700'"
                                  x-text="o.tipo_pedido === 'express' ? 'Express' : 'Pickup'"></span>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-slate-50 px-2 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Total</p>
                                <p class="mt-1 text-sm font-bold text-slate-700">₡<span x-text="o.total.toFixed(0)"></span></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-2 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Espera</p>
                                <p class="mt-1 text-sm font-bold text-slate-700"><span x-text="o.mins_waiting"></span> min</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-2 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">SLA</p>
                                <p class="mt-1 text-sm font-bold" :class="o.over_sla > 0 ? 'text-red-600' : 'text-slate-700'">
                                    <span x-text="o.sla_minutes || '-' "></span><template x-if="o.sla_minutes"><span>m</span></template>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 px-5 py-4">
                        <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Detalle</p>
                        <ul class="space-y-3 text-sm text-slate-700">
                            <template x-for="it in o.items">
                                <li class="rounded-2xl bg-slate-50 px-3.5 py-3">
                                    <div class="font-semibold text-[#071426]" x-text="it.texto"></div>
                                    <template x-if="it.detalle && it.detalle.length">
                                        <ul class="mt-2 space-y-1 text-xs text-slate-500"><template x-for="d in it.detalle"><li><span class="mr-1 text-blue-500">•</span><span x-text="d"></span></li></template></ul>
                                    </template>
                                    <template x-if="it.nota"><div class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800"><span>Nota: </span><span x-text="it.nota"></span></div></template>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700" @click="togglePriority(o)"><span x-text="o.priority ? 'Quitar prioridad' : 'Prioridad'"></span></button>
                            <button type="button" class="rounded-xl bg-blue-600 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-blue-700" @click="advance(o)"><span x-text="nextLabel(o.kitchen_status)"></span></button>
                            <button type="button" class="col-span-2 rounded-xl bg-emerald-600 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700" @click="markReady(o)" x-show="o.kitchen_status !== 'listo'">Marcar como listo</button>
                        </div>

                        <div class="mt-3 grid grid-cols-[110px_minmax(0,1fr)] gap-2">
                            <label class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5">
                                <input type="number" min="5" max="240" class="w-full border-0 bg-transparent px-0 py-2 text-xs outline-none focus:ring-0" :value="o.sla_minutes || ''" @change="updateSla(o, $event.target.value)">
                                <span class="text-[10px] font-semibold text-slate-400">min</span>
                            </label>
                            <input type="text" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 outline-none focus:border-blue-300" :value="o.notas || ''" placeholder="Nota interna de cocina" @change="updateNotes(o, $event.target.value)">
                        </div>
                    </div>
                </article>
            </template>
        </section>

        <div x-show="orders.length === 0 && !errMsg" class="mt-6 rounded-[26px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><i class="fas fa-check-double"></i></span>
            <h2 class="mt-4 text-xl font-bold text-[#071426]">Sin pedidos en esta etapa</h2>
            <p class="mt-2 text-sm text-slate-500">Cuando entren nuevos pedidos aparecerán aquí automáticamente.</p>
        </div>

        <div class="mt-6 flex justify-end text-sm text-slate-500" x-show="meta.pagination.total > meta.pagination.per_page">
            Página <span class="mx-1 font-semibold text-slate-700" x-text="meta.pagination.current_page"></span> de <span class="ml-1 font-semibold text-slate-700" x-text="meta.pagination.last_page"></span>
        </div>
    </div>
</div>
@endsection
