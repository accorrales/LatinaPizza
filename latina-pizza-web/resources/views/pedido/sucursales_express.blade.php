@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl md:text-3xl font-extrabold text-red-600">Elige la sucursal (Express)</h2>
        <a href="{{ route('express.index') }}" class="text-sm md:text-base text-gray-600 hover:text-gray-900 underline">← Cambiar dirección</a>
    </div>

    @if(session('ok'))<div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">{{ session('ok') }}</div>@endif
    @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-5 mb-6">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-red-600/10 grid place-items-center"><span class="text-red-600 font-bold">📍</span></div>
            <div class="grow">
                <div class="font-semibold text-lg">{{ $direccion['nombre'] ?? 'Dirección' }}</div>
                <div class="text-gray-700">{{ $direccion['direccion_exacta'] ?? '' }}</div>
                <div class="text-sm text-gray-500">{{ $direccion['distrito'] ?? '' }}, {{ $direccion['canton'] ?? '' }}, {{ $direccion['provincia'] ?? '' }}</div>
                @if(!empty($direccion['telefono_contacto']))<div class="text-sm text-gray-500 mt-1">Tel: {{ $direccion['telefono_contacto'] }}</div>@endif
                @if(!empty($direccion['referencias']))<div class="text-xs text-gray-400 mt-1">Ref: {{ $direccion['referencias'] }}</div>@endif
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

    <div class="bg-white rounded-2xl shadow p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xl font-semibold">Mapa</h3>
            <span class="text-sm text-gray-500">Cobertura: {{ $maxKm }} km</span>
        </div>

        @if($hasCoordsDireccion || $sucsConCoords->count())
            <div id="map"
                 data-address='{{ json_encode($direccion ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                 data-branches='{{ json_encode($sucursales ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
                 data-max-km="{{ $maxKm }}"
                 data-currency="{{ $currency }}"
                 data-maptiler-key="{{ config('services.maptiler.key') }}"
                 class="w-full min-h-[320px] md:min-h-[380px] lg:min-h-[420px] rounded-[14px] overflow-hidden"></div>
        @else
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800">No hay coordenadas para mostrar. Verifica que la dirección y sucursales tengan latitud/longitud.</div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">Sucursales disponibles</h3>
            <span class="text-sm text-gray-500">{{ !empty($sucursales) ? count($sucursales) : 0 }} encontradas</span>
        </div>

        @if(!$hayCobertura)
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800 mb-4">No hay sucursales que cubran la dirección seleccionada. Intenta con otra dirección más cercana.</div>
        @endif

        @if(empty($sucursales) || count($sucursales) === 0)
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800">No hay sucursales registradas con coordenadas válidas.</div>
        @else
            <form method="POST" action="{{ route('sucursales.express.seleccionar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="direccion_usuario_id" value="{{ $direccion['id'] }}">

                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($sucursales as $s)
                        @php
                            $covered = $s['covered'] ?? false;
                            $fee = $s['delivery_fee'] ?? null;
                            $dist = $s['distancia_km'] ?? null;
                        @endphp
                        <label class="border rounded-2xl p-4 flex gap-3 cursor-pointer hover:shadow transition-all {{ !$covered ? 'opacity-60' : '' }}">
                            <input type="radio" name="sucursal_id" value="{{ $s['id'] }}" class="mt-1" {{ !$covered ? 'disabled' : '' }} required>
                            <div class="grow">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-semibold text-lg">{{ $s['nombre'] ?? ('Sucursal #'.$s['id']) }}</div>
                                    @if($dist !== null)<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $dist }} km</span>@endif
                                    @if($covered && $fee !== null)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Delivery: {{ $currency }}{{ number_format($fee, 0, '.', ',') }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Fuera de cobertura</span>
                                    @endif
                                </div>
                                @if(!empty($s['direccion']))<div class="text-gray-700">{{ $s['direccion'] }}</div>@endif
                                <div class="text-sm text-gray-500">@if(!empty($s['telefono'])) Tel: {{ $s['telefono'] }} @endif</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="pt-2">
                    <button class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold disabled:opacity-60 disabled:cursor-not-allowed" {{ !$hayCobertura ? 'disabled' : '' }}>
                        Usar esta sucursal
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
