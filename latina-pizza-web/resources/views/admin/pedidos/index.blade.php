@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    @php
        $pedidosCollection = collect($pedidos);
    @endphp

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-br from-[#071426] via-[#0B2344] to-blue-700 px-6 py-7 text-white sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100"><i class="fa-solid fa-receipt"></i> Operación</div>
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Pedidos recibidos</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100/80">Seguimiento administrativo de pedidos, estados, sucursal y cobro.</p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[360px]">
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Activos</p><p class="mt-1 text-xl font-black">{{ $pedidosCollection->whereIn('estado', ['pendiente', 'pagado', 'preparando', 'listo'])->count() }}</p></div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Entregados</p><p class="mt-1 text-xl font-black">{{ $pedidosCollection->where('estado', 'entregado')->count() }}</p></div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Página</p><p class="mt-1 text-xl font-black">{{ count($pedidos) }}</p></div>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if (session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            <tr><th class="px-5 py-4">Pedido</th><th class="px-5 py-4">Cliente / Sucursal</th><th class="px-5 py-4">Total</th><th class="px-5 py-4">Entrega</th><th class="px-5 py-4">Estado</th><th class="px-5 py-4 text-right">Acciones</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($pedidos as $pedido)
                                @php
                                    $transiciones = match ($pedido['estado']) {
                                        'pendiente', 'pagado' => ['preparando', 'cancelado'],
                                        'preparando' => ['listo', 'cancelado'],
                                        'listo' => ['entregado', 'cancelado'],
                                        default => [],
                                    };
                                    if (($pedido['payment_provider'] ?? null) === 'stripe' && ($pedido['payment_status'] ?? null) === 'paid') {
                                        $transiciones = array_values(array_diff($transiciones, ['cancelado']));
                                    }
                                @endphp
                                <tr class="align-top transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4"><p class="text-base font-black text-slate-900">#{{ $pedido['id'] }}</p><p class="mt-1 text-xs capitalize text-slate-400">{{ $pedido['payment_provider'] ?? 'sin proveedor' }}</p></td>
                                    <td class="px-5 py-4"><p class="font-bold text-slate-900">{{ $pedido['usuario']['name'] ?? 'N/A' }}</p><p class="mt-1 text-xs text-slate-500"><i class="fa-solid fa-store mr-1 text-slate-300"></i>{{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p></td>
                                    <td class="px-5 py-4"><p class="font-black text-slate-900">₡{{ number_format($pedido['total'], 0) }}</p><p class="mt-1 text-xs text-slate-400">{{ ucfirst($pedido['payment_status'] ?? 'pendiente') }}</p></td>
                                    <td class="px-5 py-4"><span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $pedido['tipo_pedido'] === 'express' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-600' }}"><i class="fa-solid {{ $pedido['tipo_pedido'] === 'express' ? 'fa-motorcycle' : 'fa-store' }}"></i>{{ ucfirst($pedido['tipo_pedido']) }}</span></td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                                            @switch($pedido['estado'])
                                                @case('pendiente') bg-amber-50 text-amber-700 @break
                                                @case('pagado') bg-emerald-50 text-emerald-700 @break
                                                @case('preparando') bg-orange-50 text-orange-700 @break
                                                @case('listo') bg-blue-50 text-blue-700 @break
                                                @case('entregado') bg-green-50 text-green-700 @break
                                                @case('cancelado') bg-red-50 text-red-600 @break
                                                @default bg-slate-100 text-slate-600
                                            @endswitch">
                                            <span class="h-1.5 w-1.5 rounded-full current-color bg-current"></span>{{ ucfirst($pedido['estado']) }}
                                        </span>
                                        @if ($transiciones)
                                            <form method="POST" action="{{ route('admin.pedidos.estado', $pedido['id']) }}" class="mt-2" data-show-loading>
                                                @csrf @method('PUT')
                                                <select name="estado" data-auto-submit class="w-full min-w-32 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 focus:border-blue-400 focus:ring-blue-400">
                                                    <option disabled selected>Cambiar estado</option>
                                                    @foreach ($transiciones as $estado)<option value="{{ $estado }}">{{ ucfirst($estado) }}</option>@endforeach
                                                </select>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.pedidos.show', $pedido['id']) }}" data-show-loading class="inline-flex h-9 items-center gap-2 rounded-full bg-blue-50 px-4 text-xs font-bold text-blue-700 transition hover:bg-blue-100"><i class="fa-solid fa-eye"></i>Ver</a>
                                            <a href="{{ route('admin.pedidos.historial', $pedido['id']) }}" data-show-loading class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-100 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-200"><i class="fa-solid fa-clock-rotate-left"></i>Historial</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">No hay pedidos para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (($pagination['last_page'] ?? 1) > 1)
                <nav class="mt-6 flex items-center justify-center gap-3" aria-label="Paginación de pedidos">
                    @if ($pagination['current_page'] > 1)<a class="inline-flex h-10 items-center rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.pedidos.index', ['page' => $pagination['current_page'] - 1]) }}">Anterior</a>@endif
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500">Página {{ $pagination['current_page'] }} de {{ $pagination['last_page'] }}</span>
                    @if ($pagination['current_page'] < $pagination['last_page'])<a class="inline-flex h-10 items-center rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.pedidos.index', ['page' => $pagination['current_page'] + 1]) }}">Siguiente</a>@endif
                </nav>
            @endif
        </div>
    </section>
</div>
@endsection
