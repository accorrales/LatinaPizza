@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-extrabold text-red-600 mb-6 text-center">🛒 Mi Carrito</h2>

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

    @if ($carrito && isset($carrito['items']) && count($carrito['items']) > 0)
        @php $total = 0; @endphp

        <!-- Vista Mobile -->
        <div class="sm:hidden space-y-5">
            @foreach ($carrito['items'] as $item)
                @php
                    $subtotal = $item['precio_total'];
                    $total += $subtotal;
                @endphp
                <details class="bg-white border border-gray-200 rounded-xl shadow-md p-4 transition">
                    <summary class="text-lg font-bold text-red-600 cursor-pointer">{{ $item['nombre'] }} (x{{ $item['cantidad'] }})</summary>
                    <div class="mt-2 text-sm text-gray-700 space-y-2">
                        <div><strong>Tamaño:</strong> {{ $item['tamano'] ?? 'N/A' }}</div>
                        <div><strong>Sabor:</strong> {{ $item['sabor'] ?? 'N/A' }}</div>
                        <div><strong>Masa:</strong> {{ $item['masa_nombre'] ?? 'N/A' }}</div>
                        <div><strong>Nota:</strong> {{ $item['nota_cliente'] ?? '—' }}</div>

                        @if(count($item['extras']) > 0)
                            <div>
                                <strong>Extras:</strong>
                                <ul class="list-disc ml-5">
                                    @foreach($item['extras'] as $extra)
                                        <li>{{ $extra['nombre'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div><strong>Precio:</strong> <span class="text-green-600 font-bold">₡{{ number_format($item['precio_total'], 2) }}</span></div>

                        <form method="POST" action="{{ route('carrito.eliminar', ['id' => $item['id']]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="w-full mt-2 bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                                ❌ Eliminar
                            </button>
                        </form>
                    </div>
                </details>
            @endforeach
        </div>

        <!-- Vista Desktop -->
        <div class="hidden sm:block overflow-x-auto rounded-xl shadow-lg mt-4">
            <table class="min-w-full divide-y divide-gray-200 bg-white">
                <thead class="bg-red-50 text-red-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-bold">🍕 Detalle</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Cantidad</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Precio</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Subtotal</th>
                        <th class="px-6 py-3 text-center text-sm font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                    @foreach ($carrito['items'] as $item)
                        @php
                            $subtotal = $item['precio_total'];
                            $total += $subtotal;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 align-top">
                                <div class="font-semibold text-red-700 text-base">{{ $item['nombre'] }}</div>
                                <div class="text-xs text-gray-600 leading-5 mt-1">
                                    <strong>Tamaño:</strong> {{ $item['tamano'] ?? 'N/A' }}<br>
                                    <strong>Sabor:</strong> {{ $item['sabor'] ?? 'N/A' }}<br>
                                    <strong>Masa:</strong> {{ $item['masa_nombre'] ?? 'N/A' }}<br>
                                    @if($item['nota_cliente'])
                                        <strong>Nota:</strong> <em>{{ $item['nota_cliente'] }}</em><br>
                                    @endif
                                    @if(count($item['extras']) > 0)
                                        <strong>Extras:</strong>
                                        <ul class="list-disc ml-5">
                                            @foreach($item['extras'] as $extra)
                                                <li>{{ $extra['nombre'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $item['cantidad'] }}</td>
                            <td class="px-6 py-4 text-center text-green-700 font-semibold">₡{{ number_format($item['precio_total'], 2) }}</td>
                            <td class="px-6 py-4 text-center font-semibold">₡{{ number_format($subtotal, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('carrito.eliminar', ['id' => $item['id']]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm" onclick="return confirm('¿Eliminar este producto del carrito?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total y acciones -->
        <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-5">
            <div class="text-2xl font-bold text-gray-800">
                Total: <span class="text-green-600">₡{{ number_format($total, 2) }}</span>
            </div>
            <div class="flex gap-4">
                <a href="{{ url('/catalogo') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-xl font-semibold shadow transition">
                    🛍️ Seguir Comprando
                </a>
                <form method="POST" action="{{ route('carrito.checkout') }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-xl font-semibold shadow transition">
                        ✅ Confirmar Pedido
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="text-center text-gray-600 text-lg mt-10">Tu carrito está vacío.</div>
    @endif
</div>
@endsection









