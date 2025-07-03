@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">🛒 Mi Carrito</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">{{ session('error') }}</div>
    @endif

    @if ($carrito && isset($carrito['productos']) && count($carrito['productos']) > 0)
        <table class="table-auto w-full border mb-4">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2">Cantidad</th>
                    <th class="px-4 py-2">Precio</th>
                    <th class="px-4 py-2">Subtotal</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($carrito['productos'] as $producto)
                    @php
                        $subtotal = $producto['precio'] * $producto['pivot']['cantidad'];
                        $total += $subtotal;
                    @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $producto['nombre'] }}</td>
                        <td class="px-4 py-2 text-center">{{ $producto['pivot']['cantidad'] }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($producto['precio'], 2) }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($subtotal, 2) }}</td>
                        <td class="px-4 py-2 flex justify-center space-x-2">
                            <form method="POST" action="{{ route('carrito.update', ['id' => $producto['id']]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="accion" value="restar">
                                <button class="bg-yellow-300 hover:bg-yellow-400 px-2 rounded">➖</button>
                            </form>
                            <form method="POST" action="{{ route('carrito.update', ['id' => $producto['id']]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="accion" value="sumar">
                                <button class="bg-green-300 hover:bg-green-400 px-2 rounded">➕</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right text-xl font-semibold">
            Total: ₡{{ number_format($total, 2) }}
        </div>
    @else
        <p class="text-gray-600">Tu carrito está vacío.</p>
    @endif
</div>
@endsection


