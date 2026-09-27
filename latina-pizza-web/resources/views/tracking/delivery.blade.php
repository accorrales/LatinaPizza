@extends('layouts.app')
@section('title', 'Mis entregas')
@section('content')
<section id="delivery-tracking" data-orders-url="{{ route('tracking.delivery.orders') }}" data-location-base="{{ url('/repartos') }}" class="mx-auto max-w-3xl">
    <header class="mb-6 rounded-[30px] bg-[#071426] p-6 text-white">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-200">Reparto</p>
        <h1 class="mt-3 text-3xl font-bold">Mis entregas</h1>
        <p class="mt-3 text-sm text-slate-300">Compartí tu ubicación con el cliente del pedido seleccionado mientras está en camino.</p>
    </header>
    <div class="space-y-5 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
        <label class="block text-sm font-bold text-slate-700">Pedido asignado
            <select data-order class="mt-2 w-full rounded-xl border-slate-200"><option value="">Cargando entregas…</option></select>
        </label>
        <p data-address class="whitespace-pre-line text-sm text-slate-600"></p>
        <p class="text-sm text-slate-500">Mantené esta página abierta y el teléfono desbloqueado. El GPS requiere una conexión HTTPS y tu permiso. Podés detener el envío en cualquier momento.</p>
        <div class="flex flex-wrap gap-3">
            <button data-start disabled class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white disabled:opacity-40">Compartir mi ubicación</button>
            <button data-stop disabled class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold disabled:opacity-40">Detener GPS</button>
            <button data-reload class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold">Actualizar pedidos</button>
        </div>
        <p data-message role="status" aria-live="polite" class="text-sm text-slate-600"></p>
        <noscript>Activá JavaScript para compartir tu ubicación.</noscript>
    </div>
</section>
@endsection
