@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6 text-red-600">📦 Pedidos Recibidos</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="min-w-full bg-white text-sm text-gray-700">
            <thead class="bg-gray-100 text-left text-xs font-semibold uppercase">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Sucursal</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $pedido['id'] }}</td>
                        <td class="px-4 py-2">{{ $pedido['usuario']['name'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $pedido['sucursal']['nombre'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2 font-semibold text-green-600">₡{{ number_format($pedido['total'], 0) }}</td>
                        <td class="px-4 py-2 capitalize">{{ $pedido['tipo_pedido'] }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-white text-xs font-medium
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
                        </td>
                        <td class="px-4 py-2 space-x-2 flex">
                            <a href="{{ route('admin.pedidos.show', $pedido['id']) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                Ver
                            </a>

                            <a href="{{ route('admin.pedidos.historial', $pedido['id']) }}"
                               class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs">
                                Historial
                            </a>

                            <form method="POST" action="{{ route('admin.pedidos.estado', $pedido['id']) }}">
                                @csrf
                                @method('PUT')
                                <select name="estado" onchange="this.form.submit()"
                                        class="text-xs bg-gray-100 rounded px-2 py-1 border text-gray-700">
                                    <option disabled selected>Cambiar</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="preparando">Preparando</option>
                                    <option value="listo">Listo</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection




