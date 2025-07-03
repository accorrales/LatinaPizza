@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded">
    <h1 class="text-2xl font-bold mb-6">➕ Crear Nuevo Producto</h1>

    <form action="{{ route('admin.productos.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-medium">Nombre:</label>
            <input type="text" name="nombre" class="border rounded w-full p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Descripción:</label>
            <textarea name="descripcion" rows="3" class="border rounded w-full p-2" required></textarea>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Precio:</label>
            <input type="number" name="precio" class="border rounded w-full p-2" step="0.01" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">URL de la Imagen:</label>
            <input type="url" name="imagen" class="border rounded w-full p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Categoría:</label>
            <select name="categoria_id" class="border rounded w-full p-2" required>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria['id'] }}">{{ $categoria['nombre'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Estado:</label>
            <input type="checkbox" name="estado" value="1" class="mt-1">
        </div>


        <div class="flex justify-end">
            <a href="{{ route('admin.productos.index') }}" class="mr-4 text-red-600 hover:underline">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
        </div>
    </form>
</div>
@endsection



