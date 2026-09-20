@extends('layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-3xl font-bold text-red-600 mb-6 text-center">Resumen del Pedido Promocional</h2>

    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
        <h4 class="text-xl font-semibold text-blue-900">{{ $data['promocion']['nombre'] }}</h4>
        <p><strong>Precio con promoción:</strong> ₡{{ number_format($data['promocion']['precio_total'], 0, ',', '.') }}</p>
        <p><strong>Precio sin promoción:</strong> ₡{{ number_format($data['precio_sin_promocion'], 0, ',', '.') }}</p>
        <p class="text-green-600 font-semibold">💰 <strong>¡Ahorro total:</strong> ₡{{ number_format($data['ahorro_total'], 0, ',', '.') }}!</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($data['pizzas'] as $pizza)
            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 shadow-sm flex flex-col sm:flex-row items-start gap-4">
                {{-- Imagen referencial basada en sabor --}}
                <img src="{{ $pizza['imagen'] ?? asset('images/Logo.png') }}"
                     alt="Pizza {{ $pizza['sabor'] }}" class="w-24 h-24 object-cover rounded shadow-sm">

                <div class="flex-1">
                    <h5 class="text-lg font-bold text-red-700">
                        🍕 Pizza #{{ $loop->iteration }} - {{ $pizza['sabor'] }} - {{ $pizza['tamano'] }}
                    </h5>

                    <p class="mt-2"><strong>Masa:</strong> {{ $pizza['masa'] }}
                        <span class="text-sm text-gray-600">(₡{{ number_format($pizza['precio_masa'], 0, ',', '.') }})</span>
                    </p>

                    <p class="mt-2"><strong>Extras:</strong></p>
                    <ul class="list-disc list-inside text-sm">
                        @forelse ($pizza['extras'] as $extra)
                            <li>{{ $extra['nombre'] }} - ₡{{ number_format($extra['precio'], 0, ',', '.') }}</li>
                        @empty
                            <li>Sin extras</li>
                        @endforelse
                    </ul>

                    @if ($pizza['nota'])
                        <p class="mt-2"><strong>💬 Nota:</strong> <em>{{ $pizza['nota'] }}</em></p>
                    @endif

                    <p class="mt-4 font-bold text-gray-800">💵 Total de esta pizza: ₡{{ number_format($pizza['precio_total'], 0, ',', '.') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-right mt-8 font-bold text-lg">
        <p>Total sin Promo: ₡{{ number_format($data['precio_sin_promocion'], 0, ',', '.') }}</p>
        <p>Descuento: ₡{{ number_format($data['promocion']['precio_total'], 0, ',', '.') }}</p>
        <p class="text-green-600">💸 Total a Pagar: ₡{{ number_format($data['ahorro_total'], 0, ',', '.') }}</p>
    </div>
</div>
@endsection
