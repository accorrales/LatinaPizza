@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 md:py-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.16em] text-red-600">
                <i class="fas fa-motorcycle"></i>
                Express
            </span>
            <h2 class="mt-3 text-3xl font-black tracking-[-0.035em] text-slate-950 md:text-4xl">Elegí tu sucursal</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Te mostramos las sucursales más cercanas a tu dirección y su zona de cobertura.</p>
        </div>
        <a href="{{ route('express.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-red-600">
            <i class="fas fa-arrow-left text-xs"></i>
            Cambiar dirección
        </a>
    </div>

    @if(session('ok'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('ok') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-start gap-4 p-5 md:p-6">
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-slate-950 text-white shadow-lg shadow-slate-950/10">
                <i class="fas fa-location-dot"></i>
            </div>
            <div class="min-w-0 grow">
                <div class="text-xs font-extrabold uppercase tracking-[0.14em] text-red-600">Tu dirección de entrega</div>
                <div class="mt-1 text-lg font-extrabold text-slate-950">{{ $direccion['nombre'] ?? 'Dirección' }}</div>
                <div class="mt-1 text-sm leading-6 text-slate-600">{{ $direccion['direccion_exacta'] ?? '' }}</div>
                <div class="text-xs text-slate-400">{{ $direccion['distrito'] ?? '' }}, {{ $direccion['canton'] ?? '' }}, {{ $direccion['provincia'] ?? '' }}</div>
                @if(!empty($direccion['telefono_contacto']))<div class="mt-2 text-xs font-medium text-slate-500"><i class="fas fa-phone mr-1"></i>{{ $direccion['telefono_contacto'] }}</div>@endif
                @if(!empty($direccion['referencias']))<div class="mt-1 text-xs text-slate-400">Ref: {{ $direccion['referencias'] }}</div>@endif
            </div>
        </div>
    </div>

    @php
        $maxKm = $max_km ?? 10;
        $currency = $currency ?? '₡';
        $hasCoordsDireccion = !empty($direccion['latitud']) && !empty($direccion['longitud']);
        $sucsConCoords = collect($sucursales)->filter(fn($s) => !empty($s['latitud']) && !empty($s['longitud']))->values();
        $hayCobertura = collect($sucursales)->where('covered', true)->count() > 0;
    @endphp

    <div class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-red-50 text-red-600">
                    <i class="fas fa-map-location-dot"></i>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-950">Mapa de cobertura</h3>
                    <p class="text-xs text-slate-500">Tocá una sucursal para ver su radio de entrega.</p>
                </div>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                Cobertura: {{ $maxKm }} km
            </span>
        </div>

        @if($hasCoordsDireccion || $sucsConCoords->count())
            <div class="relative">
                <div id="map"
                     data-address='{{ json_encode($direccion ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                     data-branches='{{ json_encode($sucursales ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                     data-max-km="{{ $maxKm }}"
                     data-currency="{{ $currency }}"
                     data-maptiler-key="{{ config('services.maptiler.key') }}"
                     class="min-h-[360px] w-full overflow-hidden bg-slate-100 md:min-h-[440px] lg:min-h-[500px]"></div>

                <div class="pointer-events-none absolute bottom-4 left-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-lg backdrop-blur">
                        <span class="h-2.5 w-2.5 rounded bg-slate-950"></span>
                        Tu ubicación
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/70 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-lg backdrop-blur">
                        <span class="h-2.5 w-2.5 rounded bg-red-600"></span>
                        Sucursal
                    </span>
                </div>
            </div>
        @else
            <div class="m-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800 md:m-6">No hay coordenadas para mostrar. Verificá que la dirección y las sucursales tengan latitud y longitud.</div>
        @endif
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950">Sucursales disponibles</h3>
                <p class="mt-1 text-sm text-slate-500">Seleccioná la que querés usar para este pedido.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ !empty($sucursales) ? count($sucursales) : 0 }} encontradas</span>
        </div>

        @if(!$hayCobertura)
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">No hay sucursales que cubran la dirección seleccionada. Probá con otra dirección más cercana.</div>
        @endif

        @if(empty($sucursales) || count($sucursales) === 0)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">No hay sucursales registradas con coordenadas válidas.</div>
        @else
            <form method="POST" action="{{ route('sucursales.express.seleccionar') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="direccion_usuario_id" value="{{ $direccion['id'] }}">

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($sucursales as $s)
                        @php
                            $covered = $s['covered'] ?? false;
                            $fee = $s['delivery_fee'] ?? null;
                            $dist = $s['distancia_km'] ?? null;
                        @endphp

                        <label class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 transition-all has-[:checked]:border-red-500 has-[:checked]:ring-4 has-[:checked]:ring-red-50 {{ !$covered ? 'cursor-not-allowed opacity-55' : 'cursor-pointer hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg hover:shadow-slate-200/70' }}">
                            <div class="flex gap-3">
                                <input type="radio" name="sucursal_id" value="{{ $s['id'] }}" class="mt-1 h-4 w-4 border-slate-300 text-red-600 focus:ring-red-500" {{ !$covered ? 'disabled' : '' }} required>
                                <div class="min-w-0 grow">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="font-extrabold text-slate-950">{{ $s['nombre'] ?? ('Sucursal #'.$s['id']) }}</div>
                                        @if($dist !== null)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">{{ $dist }} km</span>
                                        @endif
                                    </div>

                                    @if(!empty($s['direccion']))<div class="mt-2 text-sm leading-5 text-slate-500">{{ $s['direccion'] }}</div>@endif
                                    @if(!empty($s['telefono']))<div class="mt-2 text-xs font-medium text-slate-400"><i class="fas fa-phone mr-1"></i>{{ $s['telefono'] }}</div>@endif

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        @if($covered && $fee !== null)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700">
                                                <i class="fas fa-circle-check"></i>
                                                Disponible
                                            </span>
                                            <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-extrabold text-red-600">Delivery {{ $currency }}{{ number_format($fee, 0, '.', ',') }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-500">
                                                <i class="fas fa-ban"></i>
                                                Fuera de cobertura
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none" {{ !$hayCobertura ? 'disabled' : '' }}>
                        Usar esta sucursal
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
