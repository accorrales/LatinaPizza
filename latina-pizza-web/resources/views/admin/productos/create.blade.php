@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-6">Crear nuevo producto</h2>

    <form action="{{ route('admin.productos.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block font-semibold">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        {{-- Precio --}}
        <div>
            <label for="precio" class="block font-semibold">Precio</label>
            <input type="number" name="precio" id="precio" class="w-full border rounded px-3 py-2" step="0.01" required>
        </div>

        {{-- Imagen --}}
        <div>
            <label for="imagen" class="block font-semibold">URL de Imagen</label>
            <input type="text" name="imagen" id="imagen" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Categoría --}}
        <div>
            <label for="categoria_id" class="block font-semibold">Categoría</label>
            <select name="categoria_id" id="categoria_id" class="w-full border rounded px-3 py-2" required onchange="toggleOpcionesPizza()">
                <option value="">Seleccionar...</option>
                @foreach ($categorias['data'] ?? $categorias as $categoria)
                    <option value="{{ $categoria['id'] }}">{{ $categoria['nombre'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Nombre de categoría oculto --}}
        <input type="hidden" name="categoria_nombre" id="categoria_nombre">

        {{-- Campos solo si es Pizza --}}
        <div id="camposPizza" class="space-y-4 hidden">
            {{-- Sabor --}}
            <div>
                <label for="sabor_id" class="block font-semibold">Sabor</label>
                <select name="sabor_id" id="sabor_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seleccionar...</option>
                    @foreach ($sabores['data'] ?? $sabores as $sabor)
                        <option value="{{ $sabor['id'] }}">{{ $sabor['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tamaño --}}
            <div>
                <label for="tamano_id" class="block font-semibold">Tamaño</label>
                <select name="tamano_id" id="tamano_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seleccionar...</option>
                    @foreach ($tamanos as $tamano)
                        <option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Estado --}}
        <div>
            <label for="estado" class="block font-semibold">Estado</label>
            <select name="estado" id="estado" class="w-full border rounded px-3 py-2">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>

        {{-- Botón --}}
        <div class="pt-4">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded">
                Guardar producto
            </button>
        </div>
    </form>
</div>

{{-- Script para mostrar u ocultar los campos según la categoría --}}
<script>
    function toggleOpcionesPizza() {
        const categoriaSelect = document.getElementById('categoria_id');
        const camposPizza     = document.getElementById('camposPizza');
        const categoriaNombre = categoriaSelect.options[categoriaSelect.selectedIndex]?.text ?? '';

        document.getElementById('categoria_nombre').value = categoriaNombre;

        if (categoriaNombre.toLowerCase().includes('pizza')) {
            camposPizza.classList.remove('hidden');
        } else {
            camposPizza.classList.add('hidden');
            document.getElementById('sabor_id').value = '';
            document.getElementById('tamano_id').value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', toggleOpcionesPizza);
</script>
@endsection





