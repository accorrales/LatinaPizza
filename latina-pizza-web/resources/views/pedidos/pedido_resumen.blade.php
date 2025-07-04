@extends('layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-3xl font-bold text-red-600 mb-6 text-center">Resumen del Pedido</h2>

    <div class="mb-4">
        <p><strong>ID Pedido:</strong> {{ $pedido['id'] }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($pedido['estado']) }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y h:i A') }}</p>
    </div>

    @foreach ($pedido['productos'] as $producto)
        <div class="border border-gray-300 rounded-lg p-4 mb-4 bg-gray-50">
            <h5 class="text-lg font-bold text-red-700">🍕 {{ $producto['nombre'] }}</h5>
            <p><strong>Cantidad:</strong> {{ $producto['cantidad'] }}</p>
            <p><strong>Precio unitario:</strong> ₡{{ number_format($producto['precio_unitario'], 0, ',', '.') }}</p>
            <p><strong>Subtotal:</strong> ₡{{ number_format($producto['subtotal'], 0, ',', '.') }}</p>

            @if (!empty($producto['nota']))
                <p><strong>Nota:</strong> <em>{{ $producto['nota'] }}</em></p>
            @endif
        </div>
    @endforeach

    <div class="text-right mt-8 font-bold text-lg">
        <p>Total del pedido: ₡{{ number_format($pedido['total'], 0, ',', '.') }}</p>
    </div>
</div>
@endsection
