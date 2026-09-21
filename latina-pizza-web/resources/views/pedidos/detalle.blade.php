@extends('layouts.app')

@section('meta_description', 'Consultá el detalle y estado de tu pedido en Latina Pizza.')

@section('content')
@php
    $status = $pedido['estado'] ?? 'pendiente';
    $statusClasses = match($status) {
        'pendiente' => 'bg-amber-50 text-amber-700 border-amber-200',
        'preparando' => 'bg-blue-50 text-blue-700 border-blue-200',
        'listo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'entregado' => 'bg-slate-100 text-slate-600 border-slate-200',
        'cancelado' => 'bg-red-50 text-red-700 border-red-200',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
@endphp

<div class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 bg-[#f7f9fc] text-slate-950 min-h-[70vh]">
    <div class="mx-auto max-w-[1100px] px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-14">
        <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('usuario.pedidos') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-700">
                <i class="fas fa-arrow-left text-xs"></i>
                Volver a mis pedidos
            </a>
            <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-bold uppercase tracking-[0.14em] {{ $statusClasses }}">
                {{ ucfirst($status) }}
            </span>
        </div>

        <section class="relative overflow-hidden rounded-[32px] bg-[#071426] px-6 py-8 sm:px-10 sm:py-10 text-white shadow-[0_24px_70px_rgba(7,20,38,0.18)]">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-600/30 blur-3xl"></div>
            <div class="absolute -bottom-24 right-28 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="relative z-10 grid gap-7 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.15em] text-slate-200">
                        <i class="fas fa-box"></i>
                        Detalle del pedido
                    </span>
                    <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-bold tracking-[-0.04em]">Pedido #{{ $pedido['id'] }}</h1>
                    <p class="mt-3 max-w-2xl text-sm sm:text-base leading-7 text-slate-300">
                        Acá podés revisar qué pediste, dónde se prepara y el estado actual de tu orden.
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Total</p>
                    <p class="mt-1 text-3xl font-bold text-white">₡{{ number_format($pedido['total'] ?? $pedido['ahorro_total'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-location-dot"></i></div>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.13em] text-slate-400">Sucursal</p>
                <p class="mt-1 text-sm font-semibold text-[#071426]">{{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-500"><i class="fas fa-motorcycle"></i></div>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.13em] text-slate-400">Tipo</p>
                <p class="mt-1 text-sm font-semibold text-[#071426]">{{ ucfirst($pedido['tipo_pedido'] ?? 'N/A') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="far fa-calendar"></i></div>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.13em] text-slate-400">Fecha</p>
                <p class="mt-1 text-sm font-semibold text-[#071426]">{{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y H:i') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-fire-burner"></i></div>
                <p class="mt-3 text-xs font-bold uppercase tracking-[0.13em] text-slate-400">Estado</p>
                <p class="mt-1 text-sm font-semibold text-[#071426]">{{ ucfirst($status) }}</p>
            </div>
        </section>

        <section class="mt-8">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-600">Tu orden</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#071426]">Pizzas personalizadas</h2>
                </div>
                <span class="text-sm font-medium text-slate-400">{{ count($pedido['detalles'] ?? []) }} items</span>
            </div>

            <div class="space-y-4">
                @foreach ($pedido['detalles'] as $detalle)
                    <article class="rounded-[24px] border border-slate-200 bg-white p-5 sm:p-6 shadow-[0_10px_35px_rgba(7,20,38,0.05)]">
                        <div class="flex gap-4 sm:gap-5">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                                <i class="fas fa-pizza-slice text-xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-[#071426]">{{ $detalle['sabor']['nombre'] }}</h3>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $detalle['tamano']['nombre'] }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Masa: <span class="font-semibold text-slate-700">{{ $detalle['masa']['nombre'] ?? 'Sin masa' }}</span></p>

                                @if (!empty($detalle['extras']))
                                    <div class="mt-4">
                                        <p class="text-xs font-bold uppercase tracking-[0.13em] text-slate-400">Extras</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($detalle['extras'] as $extra)
                                                <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">+ {{ $extra['nombre'] }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($detalle['nota_cliente']))
                                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-sm text-amber-900">
                                        <span class="font-semibold">Nota:</span> <span class="italic">“{{ $detalle['nota_cliente'] }}”</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-8 rounded-[28px] border border-slate-200 bg-white p-5 sm:p-6 shadow-[0_14px_45px_rgba(7,20,38,0.07)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-600">Resumen de pago</p>
                    @if(isset($pedido['precio_sin_promocion']))
                        <div class="mt-3 space-y-2 text-sm text-slate-500">
                            <p>Total sin promoción: <strong class="text-slate-700">₡{{ number_format($pedido['precio_sin_promocion'], 0, ',', '.') }}</strong></p>
                            <p>Descuento aplicado: <strong class="text-red-600">-₡{{ number_format($pedido['promocion']['precio_total'] ?? 0, 0, ',', '.') }}</strong></p>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Monto final registrado para este pedido.</p>
                    @endif
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Total a pagar</p>
                    <p class="mt-1 text-3xl font-bold text-red-600">₡{{ number_format(isset($pedido['precio_sin_promocion']) ? ($pedido['ahorro_total'] ?? 0) : ($pedido['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('usuario.pedidos') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                <i class="fas fa-receipt"></i>
                Mis pedidos
            </a>
            <a href="{{ route('catalogo.index') }}" class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-[0_10px_26px_rgba(220,38,38,0.20)] transition hover:-translate-y-0.5 hover:bg-red-700">
                <i class="fas fa-plus text-xs"></i>
                Pedir de nuevo
            </a>
        </div>
    </div>
</div>
@endsection
