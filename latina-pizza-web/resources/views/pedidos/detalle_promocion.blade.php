@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-xl shadow-xl max-w-4xl mx-auto mt-8">
    <h2 class="text-3xl font-extrabold text-red-600 mb-6 text-center">🍕 Promociones del Pedido</h2>

    <div class="bg-gray-100 p-4 rounded-lg mb-6">
        <p class="text-sm text-gray-700"><strong>ID del Pedido:</strong> {{ $pedido['pedido_id'] }}</p>
    </div>

    @foreach ($pedido['promociones'] as $promocion)
        <section class="border border-purple-200 rounded-xl p-5 mb-6">
            <h3 class="text-xl font-bold text-purple-700">{{ $promocion['nombre'] }}</h3>
            @if ($promocion['descripcion'])
                <p class="text-sm text-gray-600 mt-1">{{ $promocion['descripcion'] }}</p>
            @endif
            <p class="font-semibold mt-2">Cantidad: {{ $promocion['cantidad'] }}</p>

            <div class="mt-4 space-y-4">
                @foreach ($promocion['componentes'] as $componente)
                    <div class="border-l-4 border-red-500 bg-gray-50 p-4 rounded-lg">
                        @if ($componente['tipo'] === 'bebida')
                            <p class="font-semibold">🥤 {{ $componente['producto'] ?? 'Bebida incluida' }}</p>
                        @else
                            <p class="font-semibold">🍕 {{ $componente['sabor'] ?? 'Pizza' }}</p>
                            <p><strong>Tamaño:</strong> {{ $componente['tamano'] ?? 'No indicado' }}</p>
                            <p><strong>Masa:</strong> {{ $componente['masa'] ?? 'No indicada' }}</p>

                            @if (!empty($componente['extras']))
                                <p class="mt-2 font-medium">Extras:</p>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($componente['extras'] as $extra)
                                        <li>
                                            {{ $extra['nombre'] ?? 'Extra' }}
                                            @if (isset($extra['precio']))
                                                (₡{{ number_format($extra['precio'], 0, ',', '.') }})
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($componente['nota'])
                                <p class="mt-2 text-sm italic">📝 “{{ $componente['nota'] }}”</p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="mt-4 text-right font-bold text-purple-700">
                Total de la promoción: ₡{{ number_format($promocion['precio_total'], 0, ',', '.') }}
            </p>
        </section>
    @endforeach

    <div class="mt-8 bg-gray-100 p-6 rounded-lg text-right space-y-2">
        <p>Subtotal: ₡{{ number_format($pedido['subtotal'], 0, ',', '.') }}</p>
        <p>Entrega: ₡{{ number_format($pedido['delivery_fee'], 0, ',', '.') }}</p>
        <p class="text-2xl font-bold text-green-700">Total pagado: ₡{{ number_format($pedido['total'], 0, ',', '.') }}</p>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('usuario.pedidos') }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition duration-200 shadow">
            ⬅️ Volver a Mis Pedidos
        </a>
    </div>
</div>
@endsection
