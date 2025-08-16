@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">{{ __('viewAdmin/categorias_admin.titulo') }}</h1>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros / Búsqueda --}}
    <form method="GET" action="{{ route('admin.categorias.index') }}" class="mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="{{ __('viewAdmin/categorias_admin.buscar') }}..."
                   class="w-full sm:max-w-xs border border-gray-300 rounded px-3 py-2">
            <div class="flex gap-2">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    {{ __('viewAdmin/categorias_admin.buscar') }}
                </button>
                <a href="{{ route('admin.categorias.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">
                    {{ __('viewAdmin/categorias_admin.limpiar') }}
                </a>
            </div>
        </div>
    </form>

    {{-- Crear rápida --}}
    <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="font-semibold mb-3">{{ __('viewAdmin/categorias_admin.nueva_categoria') }}</h2>
        <form action="{{ route('admin.categorias.store') }}" method="POST" class="grid sm:grid-cols-3 gap-3">
            @csrf
            <input name="nombre" required placeholder="{{ __('viewAdmin/categorias_admin.nombre') }}"
                   class="border border-gray-300 rounded px-3 py-2">
            <input name="descripcion" placeholder="{{ __('viewAdmin/categorias_admin.descripcion') }}"
                   class="border border-gray-300 rounded px-3 py-2">
            <div class="flex items-center gap-3">
                <select name="estado" class="border border-gray-300 rounded px-3 py-2">
                    <option value="1">{{ __('viewAdmin/categorias_admin.activa') }}</option>
                    <option value="0">{{ __('viewAdmin/categorias_admin.inactiva') }}</option>
                </select>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    {{ __('viewAdmin/categorias_admin.crear') }}
                </button>
            </div>
        </form>
        @error('nombre')
            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white shadow-md rounded">
        <table class="min-w-full">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="text-left py-2 px-4">{{ __('viewAdmin/categorias_admin.nombre') }}</th>
                    <th class="text-left py-2 px-4 hidden sm:table-cell">{{ __('viewAdmin/categorias_admin.descripcion') }}</th>
                    <th class="text-left py-2 px-4">{{ __('viewAdmin/categorias_admin.estado') }}</th>
                    <th class="text-center py-2 px-4">{{ __('viewAdmin/categorias_admin.acciones') }}</th>
                </tr>
            </thead>
            <tbody class="text-gray-800">
                @forelse($categorias as $cat)
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $cat['nombre'] }}</td>
                        <td class="py-2 px-4 hidden sm:table-cell">{{ $cat['descripcion'] ?? '—' }}</td>
                        <td class="py-2 px-4">
                            @if(($cat['estado'] ?? 1) == 1)
                                <span class="inline-block bg-green-100 text-green-700 text-xs px-2 py-1 rounded">{{ __('viewAdmin/categorias_admin.activa') }}</span>
                            @else
                                <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">{{ __('viewAdmin/categorias_admin.inactiva') }}</span>
                            @endif
                        </td>
                        <td class="py-2 px-4 text-center">
                            <a href="{{ route('admin.categorias.edit', $cat['id']) }}"
                               class="text-blue-600 hover:underline mr-3">
                                {{ __('viewAdmin/categorias_admin.editar') }}
                            </a>
                            <form action="{{ route('admin.categorias.destroy', $cat['id']) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('{{ __('viewAdmin/categorias_admin.confirmar_eliminacion') }}')"
                                        class="text-red-600 hover:underline">
                                    {{ __('viewAdmin/categorias_admin.eliminar') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 px-4 text-center text-gray-500">
                            {{ __('viewAdmin/categorias_admin.sin_registros') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


</div>
@endsection


