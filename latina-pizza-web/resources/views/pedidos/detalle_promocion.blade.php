@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-xl shadow-xl max-w-4xl mx-auto mt-8">
    <h2 class="text-3xl font-extrabold text-red-600 mb-6 text-center">🍕 Detalle del Pedido con Promoción</h2>

    <div class="bg-gray-100 p-4 rounded-lg mb-6">
        <p class="text-sm text-gray-700"><strong>ID del Pedido:</strong> {{ $pedido['pedido_id'] }}</p>
        <p class="text-sm text-gray-700"><strong>🎉 Promoción:</strong> <span class="font-semibold text-purple-700">{{ $pedido['promocion']['nombre'] }}</span></p>
    </div>

    @foreach ($pedido['pizzas'] as $index => $pizza)
        <div class="border-l-4 border-red-500 bg-gray-50 p-5 rounded-lg mb-5 shadow-sm">
            <h3 class="text-xl font-semibold text-gray-800 mb-3">🍕 Pizza #{{ $index + 1 }}</h3>
            <p><strong>Sabor:</strong> {{ $pizza['sabor'] }}</p>
            <p><strong>Tamaño:</strong> {{ $pizza['tamano'] }} <span class="text-sm text-gray-500">(₡{{ number_format($pizza['precio_base'], 0, ',', '.') }})</span></p>
            <p><strong>Masa:</strong> {{ $pizza['masa'] }} <span class="text-sm text-gray-500">(₡{{ number_format($pizza['precio_masa'], 0, ',', '.') }})</span></p>

            @if (!empty($pizza['extras']))
                <div class="mt-3">
                    <p class="font-medium text-gray-700">Extras:</p>
                    <ul class="list-disc list-inside text-gray-700">
                        @foreach ($pizza['extras'] as $extra)
                            <li>{{ $extra['nombre'] }} <span class="text-sm text-gray-500">(₡{{ number_format($extra['precio'], 0, ',', '.') }})</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($pizza['nota'])
                <p class="mt-3 text-sm text-gray-600 italic">📝 "{{ $pizza['nota'] }}"</p>
            @endif

            <p class="mt-4 text-right text-green-700 font-bold">Subtotal: ₡{{ number_format($pizza['precio_total'], 0, ',', '.') }}</p>
        </div>
    @endforeach

    <div class="mt-8 bg-gray-100 p-6 rounded-lg text-right space-y-2">
        <p class="text-gray-600 text-sm">💰 <span class="font-semibold">Precio sin Promoción:</span> ₡{{ number_format($pedido['precio_sin_promocion'], 0, ',', '.') }}</p>
        <p class="text-red-600 text-sm">🎁 <span class="font-semibold">Descuento aplicado:</span> -₡{{ number_format($pedido['promocion']['precio_total'], 0, ',', '.') }}</p>
        <p class="text-2xl font-bold text-green-700">💸 Total Final: ₡{{ number_format($pedido['ahorro_total'], 0, ',', '.') }}</p>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('usuario.pedidos') }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition duration-200 shadow">
            ⬅️ Volver a Mis Pedidos
        </a>
    </div>
</div>
@endsection

