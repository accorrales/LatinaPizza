@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
    <h2 class="text-2xl font-bold mb-6 text-red-600">🧾 Detalle del Pedido #{{ $pedido['id'] }}</h2>

    <div class="bg-white shadow-md rounded p-6 space-y-4">
        <p><strong>Cliente:</strong> {{ $pedido['usuario']['name'] ?? 'N/A' }}</p>
        <p><strong>Sucursal:</strong> {{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</p>
        <p><strong>Tipo:</strong> {{ ucfirst($pedido['tipo_pedido']) }}</p>
        <p><strong>Estado:</strong> 
            <span class="px-2 py-1 text-white text-xs rounded
                @switch($pedido['estado'])
                    @case('pendiente') bg-yellow-500 @break
                    @case('preparando') bg-orange-500 @break
                    @case('listo') bg-blue-500 @break
                    @case('entregado') bg-green-600 @break
                    @case('cancelado') bg-red-600 @break
                    @default bg-gray-500
                @endswitch
            ">
                {{ ucfirst($pedido['estado']) }}
            </span>
        </p>
        <p><strong>Total:</strong> ₡{{ number_format($pedido['total'], 0) }}</p>
    </div>

    @if ($pedido['productos'])
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-3">🛍️ Productos:</h3>
            <ul class="bg-white p-4 rounded shadow space-y-2">
                @foreach ($pedido['productos'] as $prod)
                    <li class="flex justify-between border-b pb-1">
                        <span>{{ $prod['nombre'] }}</span>
                        <span class="text-gray-700 text-sm">Cantidad: {{ $prod['pivot']['cantidad'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <a href="{{ route('admin.pedidos.index') }}" class="mt-6 inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
        ← Volver a la lista
    </a>
</div>
@endsection

