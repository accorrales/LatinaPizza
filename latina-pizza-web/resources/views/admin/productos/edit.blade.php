@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Producto</h1>

   <form action="{{ route('admin.productos.update', $producto['id']) }}" method="POST">
    @csrf
    @method('PUT')
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Nombre:</label>
            <input type="text" name="nombre" value="{{ $producto['nombre'] }}" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Descripción:</label>
            <textarea name="descripcion" class="w-full border px-3 py-2 rounded">{{ $producto['descripcion'] }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Precio:</label>
            <input type="number" step="0.01" name="precio" value="{{ $producto['precio'] }}" class="w-full border px-3 py-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Imagen (URL):</label>
            <input type="text" name="imagen" value="{{ $producto['imagen'] }}" class="w-full border px-3 py-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Categoría:</label>
            <select name="categoria_id" class="w-full border px-3 py-2 rounded" required>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria['id'] }}" {{ $producto['categoria_id'] == $categoria['id'] ? 'selected' : '' }}>
                        {{ $categoria['nombre'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label>
                <input type="checkbox" name="estado" {{ $producto['estado'] ? 'checked' : '' }}>
                Activo
            </label>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar Cambios</button>
        <a href="{{ route('admin.productos.index') }}" class="ml-4 text-gray-600 hover:underline">Cancelar</a>
    </form>
</div>
@endsection
