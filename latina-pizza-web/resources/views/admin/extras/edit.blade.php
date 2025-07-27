@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-extrabold mb-6 text-center text-blue-600">
        ✏️ Editar Extra
    </h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form id="formCrearTamano" method="POST" action="{{ route('admin.extras.update', $extra->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="nombre" class="block font-semibold mb-1 text-gray-700">Nombre del extra <span class="text-red-500">*</span></label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $extra->nombre) }}"
                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror" required>
            @error('nombre')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block font-semibold mb-1 text-gray-700">₡ Precio Pequeña</label>
                <input type="number" step="0.01" name="precio_pequena" value="{{ old('precio_pequena', $extra->precio_pequena) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_pequena') border-red-500 @enderror">
                @error('precio_pequena')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">₡ Precio Mediana</label>
                <input type="number" step="0.01" name="precio_mediana" value="{{ old('precio_mediana', $extra->precio_mediana) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_mediana') border-red-500 @enderror">
                @error('precio_mediana')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">₡ Precio Grande</label>
                <input type="number" step="0.01" name="precio_grande" value="{{ old('precio_grande', $extra->precio_grande) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_grande') border-red-500 @enderror">
                @error('precio_grande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">₡ Precio Extragrande</label>
                <input type="number" step="0.01" name="precio_extragrande" value="{{ old('precio_extragrande', $extra->precio_extragrande) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_extragrande') border-red-500 @enderror">
                @error('precio_extragrande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('admin.extras.index') }}"
                onclick="mostrarLoading()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                    Cancelar
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearTamano')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                <i class="fas fa-sync-alt mr-2"></i> Actualizar Extra
            </button>
        </div>
    </form>
</div>
@endsection

