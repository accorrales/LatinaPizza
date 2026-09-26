@extends('layouts.app')
@section('title', 'Rastreo del pedido')
@section('content')
<section id="live-tracking" data-endpoint="{{ route('tracking.location', $orderId) }}" class="mx-auto max-w-5xl">
    <header class="mb-6 rounded-[30px] bg-[#071426] p-6 text-white sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-200">Tu entrega</p>
        <h1 class="mt-3 text-3xl font-bold">Pedido #{{ $orderId }}</h1>
        <p data-status class="mt-3 text-slate-300" role="status">Consultando el estado…</p>
        <a href="{{ route('usuario.pedidos') }}" class="mt-4 inline-block text-sm font-bold text-blue-200">Volver a mis pedidos</a>
    </header>
    <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
        <p data-message class="mb-4 text-sm text-slate-600" aria-live="polite">La ubicación aparece cuando el repartidor comienza a compartirla.</p>
        <p data-error role="alert" class="mb-4 text-sm text-red-700" hidden></p>
        <div data-map hidden class="relative z-0 h-96 w-full overflow-hidden rounded-2xl" aria-label="Ubicación del repartidor" role="region"></div>
        <p data-updated class="mt-4 text-xs text-slate-500"></p>
        <button data-refresh class="mt-4 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white" type="button">Actualizar</button>
        <noscript>Activá JavaScript para ver el mapa.</noscript>
    </div>
</section>
@endsection
