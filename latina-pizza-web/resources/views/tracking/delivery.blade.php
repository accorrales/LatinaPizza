@extends('layouts.app')
@section('title', 'Ruta de reparto')
@section('content')
<section id="delivery-tracking"
    data-route-url="{{ route('tracking.delivery.route') }}"
    data-location-url="{{ route('tracking.delivery.location_all') }}"
    class="mx-auto max-w-6xl space-y-6">

    <header class="overflow-hidden rounded-[32px] bg-[#071426] p-6 text-white shadow-lg sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-200">Latina Pizza · Reparto</p>
                <h1 class="mt-3 text-3xl font-black sm:text-4xl">Mi ruta de entregas</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Compartí una sola ubicación para todos tus pedidos asignados. Latina Pizza ordena las paradas, calcula los tiempos y te lleva a la siguiente entrega.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button data-start disabled type="button" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">Iniciar GPS</button>
                <button data-stop disabled type="button" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">Detener GPS</button>
                <button data-reload type="button" class="rounded-xl border border-white/20 px-5 py-3 text-sm font-bold text-white">Recalcular ruta</button>
            </div>
        </div>
        <p data-message role="status" aria-live="polite" class="mt-5 text-sm text-blue-100">Cargando tus entregas…</p>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Ruta</p>
            <p data-summary class="mt-2 text-xl font-black text-slate-900">Cargando…</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Tiempo total</p>
            <p data-total-time class="mt-2 text-xl font-black text-slate-900">—</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Distancia</p>
            <p data-distance class="mt-2 text-xl font-black text-slate-900">—</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Cálculo</p>
            <p data-provider class="mt-2 text-sm font-bold text-slate-900">Esperando ruta</p>
        </article>
    </div>

    <div data-next-card hidden class="rounded-[28px] border border-blue-200 bg-blue-50 p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">Siguiente entrega</p>
                <h2 data-next-order class="mt-2 text-2xl font-black text-slate-950"></h2>
                <p data-next-address class="mt-2 text-sm text-slate-700"></p>
                <p data-next-eta class="mt-2 text-sm font-bold text-blue-800"></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a data-waze href="#" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-[#071426] px-5 py-3 text-sm font-bold text-white">Siguiente en Waze</a>
                <a data-full-route hidden href="#" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-blue-300 bg-white px-5 py-3 text-sm font-bold text-blue-700">Ruta completa en Google Maps</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.35fr_0.85fr]">
        <article class="rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Mapa de reparto</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Paradas optimizadas</h2>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Azul: repartidor · Rojo: entregas</span>
            </div>
            <p data-map-error hidden class="mb-3 rounded-xl bg-red-50 p-3 text-sm text-red-700">No se pudo cargar el fondo del mapa. Revisá tu conexión.</p>
            <div data-map class="relative z-0 h-[480px] w-full overflow-hidden rounded-2xl bg-slate-100" aria-label="Ruta y puntos de entrega" role="region"></div>
            <p data-route-note class="mt-4 text-xs leading-5 text-slate-500">Calculando ruta…</p>
        </article>

        <aside class="space-y-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Orden de entrega</p>
                <h2 class="mt-1 text-xl font-black text-slate-900">Paradas</h2>
            </div>
            <p data-empty class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500">No tenés pedidos express en camino en este momento.</p>
            <div data-stops class="space-y-3"></div>
        </aside>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950">
        <strong>Importante:</strong> mantené esta página abierta y el teléfono desbloqueado mientras repartís. El GPS necesita HTTPS, permiso de ubicación y buena señal. La ruta se recalcula automáticamente cuando cambia tu ubicación o finaliza una entrega.
    </div>

    <noscript>Activá JavaScript para usar el mapa y compartir tu ubicación.</noscript>
</section>
@endsection
