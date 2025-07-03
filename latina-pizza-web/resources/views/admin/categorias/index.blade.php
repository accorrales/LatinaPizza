@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">📂 Gestión de Categorías</h1>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-2 mb-4 rounded">{{ session('error') }}</div>
    @endif

    <!-- Botón para abrir el modal -->
    <button onclick="document.getElementById('crearModal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">
        + Nueva Categoría
    </button>

    <!-- Tabla de categorías -->
    <table class="table-auto w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categorias as $categoria)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $categoria['nombre'] }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.categorias.edit', $categoria['id']) }}" class="text-blue-600 hover:underline">Editar</a>

                        <form action="{{ route('admin.categorias.destroy', $categoria['id']) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal de creación -->
<div id="crearModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Nueva Categoría</h2>
        <form action="{{ route('admin.categorias.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nombre" class="block text-gray-700">Nombre:</label>
                <input type="text" name="nombre" id="nombre" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('crearModal').classList.add('hidden')" class="bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

