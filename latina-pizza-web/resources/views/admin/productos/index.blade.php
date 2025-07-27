@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-red-600">📦 Productos</h1>
        <a href="{{ route('admin.productos.create') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Nuevo Producto
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 shadow">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 shadow">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="w-full text-sm text-left border border-gray-200">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3 text-center">Precio</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $producto['nombre'] }}</td>
                        <td class="px-4 py-2">{{ $producto['categoria']['nombre'] ?? 'Sin categoría' }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($producto['precio'], 0) }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($producto['estado'])
                                <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded">Activo</span>
                            @else
                                <span class="bg-red-200 text-red-800 text-xs px-2 py-1 rounded">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('admin.productos.edit', $producto['id']) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                Editar
                            </a>
                            <form action="{{ route('admin.productos.destroy', $producto['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No hay productos registrados aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

