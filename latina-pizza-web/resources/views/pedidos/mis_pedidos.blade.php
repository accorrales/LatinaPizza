@extends('layouts.app')

@section('meta_description', 'Consultá el estado y detalle de tus pedidos en Latina Pizza.')

@section('content')
@php
    $confirmedOrderId = session('pedido_confirmado_id');
@endphp

<div class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 bg-[#f7f9fc] text-slate-950 min-h-[70vh]">
    <div class="mx-auto max-w-[1180px] px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-14">
        @if($confirmedOrderId)
            <section class="relative mb-10 overflow-hidden rounded-[32px] bg-[#071426] px-6 py-8 sm:px-10 sm:py-10 text-white shadow-[0_24px_70px_rgba(7,20,38,0.20)]">
                <div class="absolute -right-16 -top-20 h-72 w-72 rounded-full bg-blue-600/30 blur-3xl"></div>
                <div class="absolute -bottom-24 right-24 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="relative z-10 grid gap-7 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-emerald-300">
                            <i class="fas fa-circle-check"></i>
                            Pedido confirmado
                        </span>
                        <h1 class="mt-4 text-3xl sm:text-4xl font-bold tracking-[-0.04em]">¡Listo! Pedido #{{ $confirmedOrderId }}</h1>
                        <p class="mt-3 max-w-2xl text-sm sm:text-base leading-7 text-slate-300">
                            Ya recibimos tu orden. Te enviamos la factura por correo y podés seguir el estado desde acá.
                        </p>
                    </div>
                    <a href="{{ route('usuario.pedidos.detalle', ['id' => $confirmedOrderId]) }}" class="inline-flex w-fit items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-blue-700 shadow-lg transition hover:-translate-y-0.5">
                        Ver pedido
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </section>
        @elseif(session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                <i class="fas fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <header class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.15em] text-blue-700">
                    <i class="fas fa-receipt"></i>
                    Tu historial
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold tracking-[-0.04em] text-[#071426]">Mis pedidos</h2>
                <p class="mt-2 text-sm sm:text-base text-slate-500">Todo lo que has pedido, en un solo lugar.</p>
            </div>
            <a href="{{ route('catalogo.index') }}" class="inline-flex w-fit items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-[0_10px_26px_rgba(220,38,38,0.20)] transition hover:-translate-y-0.5 hover:bg-red-700">
                <i class="fas fa-plus text-xs"></i>
                Nuevo pedido
            </a>
        </header>

        <section class="space-y-4">
            @forelse ($pedidos as $pedido)
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
                    $isPromo = ($pedido['tipo_contenido'] ?? null) === 'promocion';
                @endphp

                <article class="rounded-[26px] border border-slate-200 bg-white p-5 sm:p-6 shadow-[0_10px_35px_rgba(7,20,38,0.05)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_45px_rgba(7,20,38,0.09)]">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 gap-4 sm:gap-5">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $isPromo ? 'bg-violet-50 text-violet-600' : 'bg-red-50 text-red-500' }}">
                                <i class="fas {{ $isPromo ? 'fa-gift' : 'fa-pizza-slice' }} text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-[#071426]">Pedido #{{ $pedido['id'] }}</h3>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $statusClasses }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                    @if($isPromo)
                                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-violet-700">Promoción</span>
                                    @endif
                                </div>
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-500">
                                    <span class="inline-flex items-center gap-2"><i class="far fa-calendar text-slate-400"></i>{{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y h:i A') }}</span>
                                    <span class="inline-flex items-center gap-2"><i class="fas fa-coins text-slate-400"></i>Total: <strong class="text-slate-800">₡{{ number_format($pedido['total'], 0, ',', '.') }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pl-[72px] lg:pl-0">
                            @if ($isPromo)
                                <a href="{{ route('usuario.pedidos.promocion', ['id' => $pedido['id']]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                                    Ver promoción
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            @elseif (in_array($pedido['tipo_contenido'] ?? null, ['normal', 'productos']))
                                <a href="{{ route('usuario.pedidos.detalle', ['id' => $pedido['id']]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                                    Ver detalle
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            @else
                                <span class="text-sm text-slate-400">Sin detalle disponible</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[30px] border border-slate-200 bg-white px-6 py-14 text-center shadow-[0_18px_50px_rgba(7,20,38,0.06)]">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <i class="fas fa-receipt text-2xl"></i>
                    </div>
                    <h3 class="mt-5 text-2xl font-bold text-[#071426]">Todavía no tenés pedidos</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Cuando hagás tu primera orden, vas a poder seguirla y consultar todos sus detalles desde acá.</p>
                    <a href="{{ route('catalogo.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                        Ver menú
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endforelse
        </section>

        @if (($pagination['last_page'] ?? 1) > 1)
            <nav class="mt-8 flex flex-wrap items-center justify-center gap-3" aria-label="Paginación de pedidos">
                @if ($pagination['current_page'] > 1)
                    <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700"
                       href="{{ route('usuario.pedidos', ['page' => $pagination['current_page'] - 1]) }}">Anterior</a>
                @endif
                <span class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-500">
                    Página {{ $pagination['current_page'] }} de {{ $pagination['last_page'] }}
                </span>
                @if ($pagination['current_page'] < $pagination['last_page'])
                    <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700"
                       href="{{ route('usuario.pedidos', ['page' => $pagination['current_page'] + 1]) }}">Siguiente</a>
                @endif
            </nav>
        @endif
    </div>
</div>
@endsection
