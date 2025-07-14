@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">✏️ Editar Tamaño</h1>

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

    {{-- Formulario de edición --}}
    <form id="formCrearTamano" action="{{ route('admin.tamanos.update', $tamano->id) }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold text-gray-700 mb-1">Nombre del Tamaño:</label>
            <input type="text" name="nombre" id="nombre" required
                   value="{{ old('nombre', $tamano->nombre) }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Precio base --}}
        <div>
            <label for="precio_base" class="block font-semibold text-gray-700 mb-1">Precio Base (₡):</label>
            <input type="number" name="precio_base" id="precio_base" step="0.01" required
                   value="{{ old('precio_base', $tamano->precio_base) }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3" >
            <a href="{{ route('admin.tamanos.index') }}"
                onclick="mostrarLoading()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                    Cancelar
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearTamano')" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
                Actualizar Tamaño
            </button>
        </div>
    </form>
</div>
@endsection
