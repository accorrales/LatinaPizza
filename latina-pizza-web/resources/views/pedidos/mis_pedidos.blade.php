@extends('layouts.app')

@section('content')
<div class="bg-white shadow-xl rounded-lg p-6 max-w-5xl mx-auto mt-6">
    <h2 class="text-3xl font-extrabold text-center text-red-600 mb-8">🧾 Historial de Pedidos</h2>

    @forelse ($pedidos as $pedido)
        <div class="bg-gray-50 border-l-4 border-red-500 p-5 mb-6 rounded-lg shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <p class="text-sm text-gray-600">📅 <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($pedido['created_at'])->format('d/m/Y h:i A') }}</p>

                    {{-- Mostrar totales si es promoción --}}
                    @if ($pedido['tipo_contenido'] === 'promocion')
                        <p class="text-sm text-purple-700 font-semibold">🎁 Este pedido es una promoción.</p>
                        <p class="text-sm text-gray-600 italic">💡 Ver detalle para precio con descuento.</p>
                    @else
                        <p class="text-sm text-gray-600">💰 <strong>Total:</strong> ₡{{ number_format($pedido['total'], 0, ',', '.') }}</p>
                    @endif

                    <p class="text-sm text-gray-600">📦 <strong>Estado:</strong>
                        <span class="font-semibold {{ match($pedido['estado']) {
                            'pendiente' => 'text-yellow-600',
                            'preparando' => 'text-blue-600',
                            'listo' => 'text-green-600',
                            'entregado' => 'text-gray-600',
                            'cancelado' => 'text-red-600',
                            default => 'text-black'
                        } }}">{{ ucfirst($pedido['estado']) }}</span>
                    </p>
                </div>

                {{-- Botón dinámico de acción --}}
                <div class="mt-4 md:mt-0">
                    @if ($pedido['tipo_contenido'] === 'promocion')
                        <a href="{{ route('usuario.pedidos.promocion', ['id' => $pedido['id']]) }}"
                           class="inline-block px-4 py-2 bg-purple-600 text-white rounded-md shadow hover:bg-purple-700 transition">
                            🎁 Ver Promoción
                        </a>
                    @elseif ($pedido['tipo_contenido'] === 'normal' || $pedido['tipo_contenido'] === 'productos')
                        <a href="{{ route('usuario.pedidos.detalle', ['id' => $pedido['id']]) }}"
                           class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition">
                            🔍 Ver Detalle
                        </a>
                    @else
                        <span class="text-gray-500 text-sm">Sin contenido reconocible</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-gray-400">Todavía no tenés pedidos realizados.</p>
    @endforelse
</div>

@endsection


