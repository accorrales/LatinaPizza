@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-extrabold text-red-600 mb-6 text-center animate-pulse">🛒 Mi Carrito</h2>

    {{-- Mensajes de sesión --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-5 rounded-lg shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 p-3 mb-5 rounded-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @php $total = 0; @endphp

    @if ($carrito && isset($carrito['items']) && count($carrito['items']) > 0)
        {{-- 🌐 Vista Escritorio --}}
        <div class="hidden sm:block overflow-x-auto rounded-xl shadow-lg mt-4">
            <table class="min-w-full divide-y divide-gray-200 bg-white border border-gray-200">
                <thead class="bg-red-100 text-red-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold">🍕 Detalle</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Cantidad</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Precio</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                    @foreach ($carrito['items'] as $item)
                        @php
                            $subtotal = $item['precio_total'];
                            $total += $subtotal;
                            $cantidad = $item['cantidad'] ?? 1;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 align-top">
                                <div class="font-semibold text-red-700 text-base">
                                    {{ $item['tipo'] === 'producto' ? '🍕' : '🎁' }} {{ $item['nombre'] }}
                                </div>
                                <div class="text-xs text-gray-600 mt-2 space-y-1">
                                    @if($item['tipo'] === 'producto')
                                        <p><strong>Tamaño:</strong> {{ $item['tamano'] ?? '-' }}</p>
                                        <p><strong>Sabor:</strong> {{ $item['sabor'] ?? '-' }}</p>
                                        <p><strong>Masa:</strong> {{ $item['masa_nombre'] ?? '-' }}</p>
                                        @if($item['nota_cliente'])<p><em>"{{ $item['nota_cliente'] }}"</em></p>@endif
                                        @if(!empty($item['extras']))
                                            <p><strong>Extras:</strong></p>
                                            <ul class="list-disc ml-6">
                                                @foreach($item['extras'] as $extra)
                                                    <li>{{ $extra['nombre'] }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @elseif($item['tipo'] === 'promocion')
                                        <p class="text-sm text-gray-700 mb-1">{{ $item['descripcion'] }}</p>
                                        @foreach($item['pizzas'] as $pizza)
                                            @if($pizza['tipo'] === 'pizza')
                                                <div class="border p-2 rounded bg-gray-50 mb-1">
                                                    🍕 <strong>{{ $pizza['sabor']['nombre'] }}</strong> ({{ $pizza['masa']['nombre'] }})<br>
                                                    @if($pizza['nota_cliente'])<em>"{{ $pizza['nota_cliente'] }}"</em><br>@endif
                                                    @if(!empty($pizza['extras']))
                                                        <ul class="list-disc ml-5 text-gray-700">
                                                            @foreach($pizza['extras'] as $extra)
                                                                <li>{{ $extra['nombre'] }} <span class="text-gray-500">₡{{ number_format($extra['precio'], 2) }}</span></li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @elseif($pizza['tipo'] === 'bebida')
                                                <p class="text-blue-600">🥤 Bebida: {{ $pizza['producto']['nombre'] }}</p>
                                            @endif
                                        @endforeach
                                        @php
                                            $extras = 0;
                                            foreach ($item['pizzas'] as $pizza) {
                                                if (!empty($pizza['extras'])) {
                                                    foreach ($pizza['extras'] as $extra) {
                                                        $extras += $extra['precio'];
                                                    }
                                                }
                                            }
                                            $base = $item['precio_total'] - $extras;
                                        @endphp
                                        <p class="text-sm mt-2">Base: ₡{{ number_format($base, 2) }}</p>
                                        <p class="text-sm">Extras: ₡{{ number_format($extras, 2) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $cantidad }}</td>
                            <td class="px-6 py-4 text-center text-green-700 font-bold">₡{{ number_format($subtotal, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('carrito.eliminar', ['id' => $item['id']]) }}">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded shadow text-sm transition transform hover:scale-105">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 📱 Vista Móvil --}}
        <div class="sm:hidden space-y-4 mt-6">
            @foreach ($carrito['items'] as $item)
                @php
                    $subtotal = $item['precio_total'];
                    $cantidad = $item['cantidad'] ?? 1;
                @endphp
                <div class="bg-white rounded-xl shadow-md border p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-red-700 font-bold text-lg">
                                {{ $item['tipo'] === 'producto' ? '🍕 ' : '🎁 ' }}{{ $item['nombre'] }}
                            </h3>
                            <div class="text-sm text-gray-600 mt-1">
                                @if($item['tipo'] === 'producto')
                                    <p>Tamaño: {{ $item['tamano'] }}</p>
                                    <p>Sabor: {{ $item['sabor'] }}</p>
                                    <p>Masa: {{ $item['masa_nombre'] }}</p>
                                    @if($item['nota_cliente'])<p><em>"{{ $item['nota_cliente'] }}"</em></p>@endif
                                    @if (!empty($item['extras']))
                                        <p class="mt-1">Extras:</p>
                                        <ul class="list-disc ml-5 text-sm">
                                            @foreach ($item['extras'] as $extra)
                                                <li>{{ $extra['nombre'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @elseif($item['tipo'] === 'promocion')
                                    <p class="mb-1">{{ $item['descripcion'] }}</p>
                                    @foreach($item['pizzas'] as $pizza)
                                        @if($pizza['tipo'] === 'pizza')
                                            <div class="border p-2 rounded bg-gray-50 mb-1">
                                                🍕 <strong>{{ $pizza['sabor']['nombre'] }}</strong> ({{ $pizza['masa']['nombre'] }})<br>
                                                @if($pizza['nota_cliente']) <em>"{{ $pizza['nota_cliente'] }}"</em><br>@endif
                                                @if(!empty($pizza['extras']))
                                                    <ul class="list-disc ml-5 text-sm text-gray-600">
                                                        @foreach($pizza['extras'] as $extra)
                                                            <li>{{ $extra['nombre'] }} <span class="text-gray-500">₡{{ number_format($extra['precio'], 2) }}</span></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @elseif($pizza['tipo'] === 'bebida')
                                            <p class="text-blue-600">🥤 Bebida: {{ $pizza['producto']['nombre'] }}</p>
                                        @endif
                                    @endforeach
                                    @php
                                        $extras = 0;
                                        foreach ($item['pizzas'] as $pizza) {
                                            if (!empty($pizza['extras'])) {
                                                foreach ($pizza['extras'] as $extra) {
                                                    $extras += $extra['precio'];
                                                }
                                            }
                                        }
                                        $base = $item['precio_total'] - $extras;
                                    @endphp
                                    <div class="text-sm mt-2 text-gray-700">
                                        Base: ₡{{ number_format($base, 2) }}<br>
                                        Extras: ₡{{ number_format($extras, 2) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right text-green-700 font-bold">₡{{ number_format($subtotal, 2) }}</div>
                    </div>

                    <div class="flex justify-between items-center text-sm mt-1">
                        <span class="text-gray-500">Cantidad: {{ $cantidad }}</span>
                        <form method="POST" action="{{ route('carrito.eliminar', ['id' => $item['id']]) }}">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 💰 Total y acciones --}}
        <div class="mt-10 flex flex-col sm:flex-row justify-between items-center gap-6">
            <div class="text-3xl font-bold text-gray-800">
                Total: <span class="text-green-600">₡{{ number_format($total, 2) }}</span>
            </div>
            <div class="flex gap-4">
                <a href="{{ url('/catalogo') }}"
                    onclick="mostrarLoading();"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-xl font-semibold shadow-md transition hover:scale-105">
                        🔄 Seguir Comprando
                </a>
                <form method="POST" action="{{ route('carrito.checkout') }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-xl font-semibold shadow-md transition hover:scale-105">
                        ✅ Confirmar Pedido
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="text-center text-gray-600 text-lg mt-10 animate-fade-in">Tu carrito está vacío.</div>
    @endif
</div>
@endsection













