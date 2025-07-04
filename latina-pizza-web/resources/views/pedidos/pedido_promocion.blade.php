@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6 bg-white rounded-xl shadow-md mt-6">
    <h2 class="text-2xl font-bold mb-4">📦 Detalle del Pedido #{{ $pedido['id'] }}</h2>

    {{-- Info general --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <p><span class="font-semibold">Estado:</span>
                <span class="inline-block px-2 py-1 rounded-full text-white
                    @if($pedido['estado'] == 'pendiente') bg-yellow-500
                    @elseif($pedido['estado'] == 'preparando') bg-blue-500
                    @elseif($pedido['estado'] == 'listo') bg-green-500
                    @elseif($pedido['estado'] == 'entregado') bg-emerald-600
                    @else bg-red-500 @endif">
                    {{ ucfirst($pedido['estado']) }}
                </span>
            </p>
            <p><span class="font-semibold">Tipo:</span> {{ ucfirst($pedido['tipo_pedido']) }}</p>
        </div>
        <div>
            <p><span class="font-semibold">Fecha:</span> {{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y H:i') }}</p>
            <p><span class="font-semibold">Sucursal:</span> {{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Promoción aplicada --}}
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
        <h3 class="text-lg font-bold text-blue-700 mb-1">🎁 Promoción aplicada</h3>
        <p><strong>Nombre:</strong> {{ $pedido['promocion']['nombre'] }}</p>
        <p><strong>Descripción:</strong> {{ $pedido['promocion']['descripcion'] ?? 'Sin descripción' }}</p>
    </div>

    {{-- Pizzas dentro de la promo --}}
    <h3 class="text-xl font-semibold mb-3">🍕 Pizzas de la Promoción</h3>
    <div class="grid gap-4 mb-6">
        @foreach ($pedido['detalles_promocion'] as $detalle)
            <div class="p-4 border rounded-md shadow-sm bg-gray-50">
                <p><strong>Sabor:</strong> {{ $detalle['sabor']['nombre'] }}</p>
                <p><strong>Tamaño:</strong> {{ $detalle['tamano']['nombre'] }}</p>
                <p><strong>Masa:</strong> {{ $detalle['masa']['nombre'] ?? 'Sin masa' }}</p>
                @if ($detalle['nota_cliente'])
                    <p><strong>Nota:</strong> {{ $detalle['nota_cliente'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Total visual --}}
    <div class="mt-6 bg-gray-100 p-4 rounded-lg shadow-sm text-right">
        <p class="text-sm text-gray-500 mb-1">
            🎁 <span class="font-semibold">Valor Promo:</span>
            ₡{{ number_format($pedido['promocion']['precio_total'], 0, ',', '.') }}
        </p>
        <p class="text-xl font-bold text-green-600 mt-2">
            💸 Total a Pagar:
            ₡{{ number_format($pedido['ahorro_total'], 0, ',', '.') }}
        </p>
    </div>
</div>
@endsection
