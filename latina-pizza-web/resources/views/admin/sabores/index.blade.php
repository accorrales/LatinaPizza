@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">🍕 Gestión de Sabores</h1>

    {{-- Mensajes de sesión --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 border border-green-300 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Botón para crear nuevo sabor --}}
    <div class="mb-4 text-right">
        <a href="{{ route('admin.sabores.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
            + Nuevo Sabor
        </a>
    </div>

    {{-- Tabla de sabores --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Nombre</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Descripción</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">Imagen</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                @forelse ($sabores as $sabor)
                    <tr>
                        <td class="px-6 py-4">{{ $sabor['nombre'] }}</td>
                        <td class="px-6 py-4">{{ $sabor['descripcion'] ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if (!empty($sabor['imagen']))
                                <img src="{{ $sabor['imagen'] }}" alt="{{ $sabor['nombre'] }}" class="h-12 rounded shadow">
                            @else
                                <span class="text-gray-400 italic">Sin imagen</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.sabores.edit', $sabor['id']) }}" class="text-blue-600 hover:underline mr-3">
                                Editar
                            </a>

                            <form action="{{ route('admin.sabores.destroy', $sabor['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este sabor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay sabores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

