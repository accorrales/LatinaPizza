@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-red-600">{{ __('viewAdmin/productos_admin.index.titulo') }}</h1>
        <a href="{{ route('admin.productos.create') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> {{ __('viewAdmin/productos_admin.index.nuevo') }}
        </a>
    </div>

    <!-- Mensajes de éxito o error -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 shadow">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 shadow">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla de productos -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="w-full text-sm text-left border border-gray-200">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">{{ __('viewAdmin/productos_admin.index.nombre') }}</th>
                    <th class="px-4 py-3">{{ __('viewAdmin/productos_admin.index.categoria') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('viewAdmin/productos_admin.index.precio') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('viewAdmin/productos_admin.index.estado') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('viewAdmin/productos_admin.index.acciones') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $producto['nombre'] }}</td>
                        <td class="px-4 py-2">{{ $producto['categoria']['nombre'] ?? __('viewAdmin/productos_admin.index.sin_categoria') }}</td>
                        <td class="px-4 py-2 text-center">
                            ₡{{ number_format($producto['precio'], 0) }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($producto['estado'])
                                <span class="bg-green-200 text-green-800 text-xs px-2 py-1 rounded">{{ __('viewAdmin/productos_admin.index.activo') }}</span>
                            @else
                                <span class="bg-red-200 text-red-800 text-xs px-2 py-1 rounded">{{ __('viewAdmin/productos_admin.index.inactivo') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <!-- Botón editar -->
                            <a href="{{ route('admin.productos.edit', $producto['id']) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">
                                {{ __('viewAdmin/productos_admin.index.editar') }}
                            </a>

                            <!-- Botón eliminar -->
                            <form action="{{ route('admin.productos.destroy', $producto['id']) }}"
                                  method="POST"
                                  class="inline-block"
                                  onsubmit="return confirm('{{ __('viewAdmin/productos_admin.index.confirmar_eliminar') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    {{ __('viewAdmin/productos_admin.index.eliminar') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">
                            {{ __('viewAdmin/productos_admin.index.vacio') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection




