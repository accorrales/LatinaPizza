@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')
<div class="mx-auto max-w-6xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-br from-[#071426] via-[#0B2344] to-blue-700 px-6 py-7 text-white sm:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100"><i class="fa-solid fa-receipt"></i> Pedido #{{ $pedido['id'] }}</div>
                    <h1 class="text-3xl font-black tracking-tight">Detalle del pedido</h1>
                    <p class="mt-2 text-sm text-blue-100/80">Información comercial, entrega, pago y productos asociados.</p>
                </div>
                <a href="{{ route('admin.pedidos.historial', $pedido['id']) }}" data-show-loading class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-white/10 px-5 text-sm font-bold text-white ring-1 ring-white/15 transition hover:bg-white/15"><i class="fa-solid fa-clock-rotate-left"></i>Ver historial</a>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if (session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Cliente</p><p class="mt-2 font-black text-slate-900">{{ $pedido['usuario']['name'] ?? 'N/A' }}</p></div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Sucursal</p><p class="mt-2 font-black text-slate-900">{{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p></div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Entrega</p><p class="mt-2 font-black capitalize text-slate-900">{{ $pedido['tipo_pedido'] }}</p></div>
                <div class="rounded-3xl border border-red-100 bg-red-50 p-5"><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-red-400">Total</p><p class="mt-2 text-2xl font-black text-red-600">₡{{ number_format($pedido['total'], 0) }}</p></div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
                <div class="rounded-3xl border border-slate-200 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Estado del pedido</p><div class="mt-2"><span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold @switch($pedido['estado']) @case('pendiente') bg-amber-50 text-amber-700 @break @case('pagado') bg-emerald-50 text-emerald-700 @break @case('preparando') bg-orange-50 text-orange-700 @break @case('listo') bg-blue-50 text-blue-700 @break @case('entregado') bg-green-50 text-green-700 @break @case('cancelado') bg-red-50 text-red-600 @break @default bg-slate-100 text-slate-600 @endswitch"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ ucfirst($pedido['estado']) }}</span></div></div>
                        <div class="sm:text-right"><p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Pago</p><p class="mt-2 text-sm font-black capitalize text-slate-900">{{ ucfirst($pedido['payment_status'] ?? 'pendiente') }}</p><p class="mt-1 text-xs capitalize text-slate-400">{{ $pedido['payment_provider'] ?? 'Sin proveedor' }}</p></div>
                    </div>

                    @if (($pedido['payment_provider'] ?? null) === 'stripe' && in_array(($pedido['payment_status'] ?? null), ['paid', 'refund_pending'], true))
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div><p class="font-black text-red-800">Reembolso de pago</p><p class="mt-1 text-xs leading-5 text-red-600">Disponible para pagos Stripe confirmados o pendientes de reembolso.</p></div>
                                <form method="POST" action="{{ route('admin.pedidos.refund', $pedido['id']) }}" data-confirm="¿Confirma el reembolso total de este pedido? Esta acción no se puede deshacer." data-show-loading>
                                    @csrf
                                    <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-red-600 px-5 text-xs font-bold text-white transition hover:bg-red-700"><i class="fa-solid fa-arrow-rotate-left"></i>Reembolsar pago completo</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl bg-[#071426] p-6 text-white">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Resumen</p>
                    <div class="mt-5 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><span class="text-slate-400">Pedido</span><strong>#{{ $pedido['id'] }}</strong></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-400">Tipo</span><strong class="capitalize">{{ $pedido['tipo_pedido'] }}</strong></div>
                        <div class="flex justify-between gap-4"><span class="text-slate-400">Estado</span><strong class="capitalize">{{ $pedido['estado'] }}</strong></div>
                        <div class="h-px bg-white/10"></div>
                        <div class="flex items-end justify-between gap-4"><span class="text-slate-400">Total</span><strong class="text-2xl text-white">₡{{ number_format($pedido['total'], 0) }}</strong></div>
                    </div>
                </div>
            </div>

            @if ($pedido['productos'])
                <div class="mt-6 rounded-3xl border border-slate-200 p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3"><div class="grid h-10 w-10 place-items-center rounded-2xl bg-blue-50 text-blue-600"><i class="fa-solid fa-bag-shopping"></i></div><div><h2 class="font-black text-slate-900">Productos</h2><p class="text-xs text-slate-500">Contenido registrado en el pedido.</p></div></div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($pedido['productos'] as $prod)
                            <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0"><div class="flex items-center gap-3"><div class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-50 text-red-500"><i class="fa-solid fa-pizza-slice"></i></div><span class="font-bold text-slate-900">{{ $prod['nombre'] }}</span></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Cantidad: {{ $prod['pivot']['cantidad'] }}</span></div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6"><a href="{{ route('admin.pedidos.index') }}" data-show-loading class="inline-flex h-11 items-center gap-2 rounded-full border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i>Volver a pedidos</a></div>
        </div>
    </section>
</div>
@endsection
