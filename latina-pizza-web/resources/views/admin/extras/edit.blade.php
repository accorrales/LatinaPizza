@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6">Editar Extra</h2>

    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.extras.update', $extra->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block font-semibold mb-1">Nombre del extra</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $extra->nombre) }}"
                class="w-full border border-gray-300 rounded px-3 py-2 @error('nombre') border-red-500 @enderror" required>
            @error('nombre')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Precio Pequeña</label>
                <input type="number" name="precio_pequena" step="0.01" value="{{ old('precio_pequena', $extra->precio_pequena) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_pequena') border-red-500 @enderror">
                @error('precio_pequena')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Precio Mediana</label>
                <input type="number" name="precio_mediana" step="0.01" value="{{ old('precio_mediana', $extra->precio_mediana) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_mediana') border-red-500 @enderror">
                @error('precio_mediana')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Precio Grande</label>
                <input type="number" name="precio_grande" step="0.01" value="{{ old('precio_grande', $extra->precio_grande) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_grande') border-red-500 @enderror">
                @error('precio_grande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1">Precio Extragrande</label>
                <input type="number" name="precio_extragrande" step="0.01" value="{{ old('precio_extragrande', $extra->precio_extragrande) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_extragrande') border-red-500 @enderror">
                @error('precio_extragrande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex justify-between">
            <a href="{{ route('admin.extras.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
    </form>
</div>
@endsection
