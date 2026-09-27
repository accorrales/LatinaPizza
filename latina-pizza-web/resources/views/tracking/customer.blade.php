@extends('layouts.app')
@section('title', 'Rastreo del pedido')
@section('content')
<section id="live-tracking" data-endpoint="{{ route('tracking.location', $orderId) }}" class="mx-auto max-w-5xl space-y-6">
    <header class="rounded-[30px] bg-[#071426] p-6 text-white sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-200">Tu entrega</p>
        <h1 class="mt-3 text-3xl font-bold">Pedido #{{ $orderId }}</h1>
        <p data-status class="mt-3 text-slate-300" role="status">Consultando el estado…</p>
        <a href="{{ route('usuario.pedidos') }}" class="mt-4 inline-block text-sm font-bold text-blue-200">Volver a mis pedidos</a>
    </header>

    <div data-eta-card hidden class="grid gap-4 sm:grid-cols-3">
        <article class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Llegada estimada</p>
            <p data-eta class="mt-2 text-3xl font-black text-slate-950">—</p>
            <p data-eta-duration class="mt-2 text-sm text-slate-600"></p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Tu posición en la ruta</p>
            <p data-queue class="mt-2 text-lg font-bold text-slate-900"></p>
            <p data-estimate-note class="mt-2 text-xs leading-5 text-slate-500"></p>
        </article>
    </div>

    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Mapa en vivo</p>
                <p data-message class="mt-1 text-sm text-slate-600" aria-live="polite">La ubicación aparece cuando el repartidor comienza a compartirla.</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold text-slate-500">
                <span>🔵 Repartidor</span>
                <span>🔴 Tu entrega</span>
            </div>
        </div>
        <p data-error role="alert" class="mb-4 text-sm text-red-700" hidden></p>
        <div data-map hidden class="relative z-0 h-96 w-full overflow-hidden rounded-2xl" aria-label="Ubicación del repartidor y tu punto de entrega" role="region"></div>
        <p data-updated class="mt-4 text-xs text-slate-500"></p>
        <button data-refresh class="mt-4 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white" type="button">Actualizar</button>
        <noscript>Activá JavaScript para ver el mapa.</noscript>
    </div>

    <p class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs leading-5 text-slate-500">Por privacidad, este mapa solo muestra tu punto de entrega. Si el repartidor lleva otros pedidos en la misma ruta, sus direcciones no se comparten con otros clientes.</p>
</section>
@endsection
