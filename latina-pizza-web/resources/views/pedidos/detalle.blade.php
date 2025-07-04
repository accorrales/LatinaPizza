@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10 p-8 bg-white rounded-2xl shadow-2xl">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">📦 Detalle del Pedido #{{ $pedido['id'] }}</h2>

    {{-- Información general del pedido --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg mb-8 shadow-inner">
        <div>
            <p class="text-sm text-gray-600 mb-1"><strong class="text-gray-800">Estado:</strong>
                <span class="inline-block px-3 py-1 text-sm rounded-full text-white font-semibold
                    @if($pedido['estado'] == 'pendiente') bg-yellow-500
                    @elseif($pedido['estado'] == 'preparando') bg-blue-500
                    @elseif($pedido['estado'] == 'listo') bg-green-500
                    @elseif($pedido['estado'] == 'entregado') bg-emerald-600
                    @else bg-red-500 @endif">
                    {{ ucfirst($pedido['estado']) }}
                </span>
            </p>
            <p class="text-sm text-gray-600"><strong class="text-gray-800">Tipo:</strong> {{ ucfirst($pedido['tipo_pedido']) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600"><strong class="text-gray-800">Fecha:</strong> {{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y H:i') }}</p>
            <p class="text-sm text-gray-600"><strong class="text-gray-800">Sucursal:</strong> {{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Pizzas personalizadas --}}
    <h3 class="text-2xl font-semibold text-red-600 mb-4">🍕 Pizzas Personalizadas</h3>
    <div class="space-y-6">
        @foreach ($pedido['detalles'] as $detalle)
            <div class="p-6 border border-gray-200 rounded-xl bg-gray-50 shadow-sm">
                <p><strong class="text-gray-700">Sabor:</strong> {{ $detalle['sabor']['nombre'] }}</p>
                <p><strong class="text-gray-700">Tamaño:</strong> {{ $detalle['tamano']['nombre'] }}</p>
                <p><strong class="text-gray-700">Masa:</strong> {{ $detalle['masa']['nombre'] ?? 'Sin masa' }}</p>

                @if (!empty($detalle['extras']))
                    <div class="mt-3">
                        <p class="font-medium text-gray-700">Extras:</p>
                        <div class="flex flex-wrap mt-1">
                            @foreach ($detalle['extras'] as $extra)
                                <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-medium px-3 py-1 rounded-full mr-2 mb-2 shadow-sm">
                                    {{ $extra['nombre'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($detalle['nota_cliente'])
                    <p class="mt-3 text-sm italic text-gray-600">📝 "{{ $detalle['nota_cliente'] }}"</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Totales --}}
    <div class="mt-10 bg-gray-100 p-6 rounded-lg shadow-inner text-right space-y-2">
        @if(isset($pedido['precio_sin_promocion']))
            <p class="text-sm text-gray-600">
                💰 <span class="font-semibold">Total sin Promoción:</span> ₡{{ number_format($pedido['precio_sin_promocion'], 0, ',', '.') }}
            </p>
            <p class="text-sm text-red-600">
                🎁 <span class="font-semibold">Descuento aplicado:</span> -₡{{ number_format($pedido['promocion']['precio_total'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="text-2xl font-bold text-green-700 mt-3">
                💸 Total a Pagar: ₡{{ number_format($pedido['ahorro_total'], 0, ',', '.') }}
            </p>
        @else
            <p class="text-2xl font-bold text-green-700">
                💸 Total a Pagar: ₡{{ number_format($pedido['total'], 0, ',', '.') }}
            </p>
        @endif
    </div>
</div>
@endsection



