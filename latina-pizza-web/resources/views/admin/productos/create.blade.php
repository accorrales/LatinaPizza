@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 bg-white shadow rounded-xl">
    <h2 class="text-3xl font-extrabold mb-6 text-center text-red-600">➕ Nuevo Producto</h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.productos.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block font-semibold mb-1 text-gray-700">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre') }}"
                   class="w-full border border-gray-300 rounded px-4 py-2 @error('nombre') border-red-500 @enderror" required>
            @error('nombre')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-semibold mb-1 text-gray-700">Descripción</label>
            <textarea name="descripcion"
                      class="w-full border border-gray-300 rounded px-4 py-2 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1 text-gray-700">₡ Precio</label>
                <input type="number" step="0.01" name="precio" value="{{ old('precio') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 @error('precio') border-red-500 @enderror" required>
                @error('precio')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">URL de Imagen</label>
                <input type="text" name="imagen" value="{{ old('imagen') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 @error('imagen') border-red-500 @enderror">
                @error('imagen')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block font-semibold mb-1 text-gray-700">Categoría</label>
            <select name="categoria_id"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('categoria_id') border-red-500 @enderror" required>
                <option value="">-- Seleccione una categoría --</option>
                @if(is_array($categorias))
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria['id'] }}">{{ $categoria['nombre'] }}</option>
                    @endforeach
                @endif
            </select>
            @error('categoria_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="estado" id="estado" {{ old('estado') ? 'checked' : '' }}>
            <label for="estado" class="text-gray-700">Producto activo</label>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('admin.productos.index') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Cancelar
            </a>
            <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                <i class="fas fa-save mr-2"></i> Guardar Producto
            </button>
        </div>
    </form>
</div>
@endsection


