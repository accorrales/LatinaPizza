@extends('layouts.app')

@section('meta_description', 'Rastreo interno de pedidos de Latina Pizza.')

@section('content')
<div id="tracking-board" data-live-base="{{ url('/mis-pedidos') }}" data-endpoint="{{ route('tracking.orders') }}" data-admin-base="{{ url('/admin/pedidos') }}" class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 min-h-[75vh] bg-[#f4f7fb] text-slate-950">
    <div class="mx-auto max-w-[1360px] px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
        <header class="mb-7 rounded-[30px] bg-[#071426] px-5 py-6 text-white shadow-[0_24px_70px_rgba(7,20,38,0.16)] sm:px-7 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-200"><i class="fas fa-route" aria-hidden="true"></i> Seguimiento interno</span>
                    <h1 class="mt-4 text-3xl font-bold tracking-[-0.04em] sm:text-4xl lg:text-5xl">Rastreo de pedidos</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-base">De la recepción a la entrega. Consultá el avance, la sucursal y el historial de cada pedido.</p>
                    <p class="mt-2 text-xs text-slate-400">Actualización cada 15 segundos · Consultá el mapa desde cada pedido.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('kitchen.index') }}" class="inline-flex h-12 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold hover:bg-white/10">Ir a cocina</a>
                    <button data-refresh type="button" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-white px-4 text-sm font-bold text-[#071426] hover:bg-blue-50 disabled:opacity-50"><i class="fas fa-rotate-right text-blue-600" aria-hidden="true"></i> Actualizar</button>
                </div>
            </div>
        </header>

        <form data-filters class="mb-6 grid gap-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-xs font-bold text-slate-500">Pedido o cliente
                <input name="search" maxlength="100" placeholder="Número o nombre del cliente" class="mt-2 block w-full rounded-xl border-slate-200 text-sm font-normal focus:border-blue-500 focus:ring-blue-500">
            </label>
            <label class="text-xs font-bold text-slate-500">Estado
                <select name="estado" class="mt-2 block w-full rounded-xl border-slate-200 text-sm font-normal focus:border-blue-500 focus:ring-blue-500">
                    <option value="activos">Pedidos activos</option><option value="todos">Todos los estados</option><option value="pendiente">Pendiente</option><option value="pagado">Pagado</option><option value="preparando">En preparación</option><option value="listo">Listo para entrega</option><option value="en_camino">En camino</option><option value="entregado">Entregado</option><option value="cancelado">Cancelado</option>
                </select>
            </label>
            <label class="text-xs font-bold text-slate-500">Método de entrega
                <select name="tipo" class="mt-2 block w-full rounded-xl border-slate-200 text-sm font-normal focus:border-blue-500 focus:ring-blue-500"><option value="">Express y Pickup</option><option value="express">Express</option><option value="pickup">Pickup</option></select>
            </label>
            <button class="self-end rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700" type="submit">Buscar pedidos</button>
        </form>
        <div class="mb-5 flex flex-wrap justify-between gap-3 text-sm text-slate-500"><p data-summary role="status" aria-live="polite">Cargando pedidos…</p><p data-updated>Sin sincronizar</p></div>
        <p data-error role="alert" class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" hidden></p>
        <p data-empty class="rounded-[24px] border border-slate-200 bg-white p-10 text-center text-slate-500" hidden>No hay pedidos que coincidan con los filtros.</p>
        <noscript><p class="rounded-2xl bg-amber-50 p-5 text-amber-800">Activá JavaScript para consultar y actualizar el rastreo de pedidos.</p></noscript>
        <section data-orders class="grid gap-5 lg:grid-cols-2" aria-label="Pedidos" aria-busy="true"></section>
        <nav class="mt-6 flex items-center justify-center gap-4" aria-label="Páginas de pedidos">
            <button data-prev type="button" disabled class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold disabled:opacity-40">Anterior</button>
            <span data-page class="text-sm text-slate-500">Página 1</span>
            <button data-next type="button" disabled class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold disabled:opacity-40">Siguiente</button>
        </nav>
        <template data-card>
            <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_10px_32px_rgba(7,20,38,0.05)]">
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h2 data-id class="text-xl font-bold tracking-tight text-[#071426]"></h2><span data-state class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700"></span></div>
                    <p data-client class="mt-3 font-semibold text-slate-800"></p><p data-branch class="mt-1 text-sm text-slate-500"></p>
                </div>
                <div class="space-y-5 p-5">
                    <ol data-progress class="grid grid-cols-5 gap-2 text-center text-xs" aria-label="Avance del pedido"></ol>
                    <dl class="grid grid-cols-2 gap-4 rounded-2xl bg-slate-50 p-4 text-sm">
                        <div><dt class="text-xs text-slate-500">Recibido</dt><dd data-created class="mt-1 font-semibold"></dd></div>
                        <div><dt class="text-xs text-slate-500">Total</dt><dd data-total class="mt-1 font-semibold"></dd></div>
                        <div><dt class="text-xs text-slate-500">Hora comprometida</dt><dd data-promised class="mt-1 font-semibold"></dd></div>
                        <div><dt class="text-xs text-slate-500">Listo desde</dt><dd data-ready class="mt-1 font-semibold"></dd></div>
                    </dl>
                    <a data-live-link class="inline-flex text-sm font-bold text-blue-600 hover:underline">Ver ubicación de entrega →</a>
                    <details data-details class="rounded-2xl border border-slate-200 p-4">
                        <summary class="cursor-pointer text-sm font-bold text-blue-600">Entrega e historial</summary>
                        <p data-address class="mt-4 whitespace-pre-line break-words text-sm leading-6 text-slate-600"></p>
                        <h3 class="mb-2 mt-5 text-xs font-bold uppercase tracking-wide text-slate-400">Historial registrado</h3>
                        <ol data-history class="space-y-2 text-sm text-slate-600"></ol>
                    </details>
                    @if(Auth::user()->role === 'admin')
                        <a data-admin-link class="inline-flex text-sm font-bold text-blue-600 hover:underline">Abrir gestión del pedido →</a>
                    @endif
                </div>
            </article>
        </template>
    </div>
</div>
@endsection
