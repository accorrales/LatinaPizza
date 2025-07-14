@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">➕ Nuevo Sabor</h1>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4">
            <ul class="list-disc ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario de creación --}}
    <form id="formCrearTamano" action="{{ route('admin.sabores.store') }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md" enctype="multipart/form-data">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold text-gray-700 mb-1">Nombre del Sabor:</label>
            <input type="text" name="nombre" id="nombre" required
                   value="{{ old('nombre') }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block font-semibold text-gray-700 mb-1">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                      class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('descripcion') }}</textarea>
        </div>

        {{-- Imagen --}}
        <div>
            <label for="imagen" class="block font-semibold text-gray-700 mb-1">URL de la Imagen:</label>
            <input type="url" name="imagen" id="imagen"
                   placeholder="https://cdn.example.com/pizzas/sabor.jpg"
                   value="{{ old('imagen') }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.sabores.index') }}"
                onclick="mostrarLoading()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                    Cancelar
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearTamano')" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                Guardar Sabor
            </button>
        </div>
    </form>
</div>
@endsection

