@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-6">{{ __('viewAdmin/productos_admin.create.titulo') }}</h2>

    <form action="{{ route('admin.productos.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.nombre') }}</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.descripcion') }}</label>
            <textarea name="descripcion" id="descripcion" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        {{-- Precio --}}
        <div>
            <label for="precio" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.precio') }}</label>
            <input type="number" name="precio" id="precio" class="w-full border rounded px-3 py-2" step="0.01" required>
        </div>

        {{-- Imagen --}}
        <div>
            <label for="imagen" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.imagen') }}</label>
            <input type="text" name="imagen" id="imagen" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Categoría --}}
        <div>
            <label for="categoria_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.categoria') }}</label>
            <select name="categoria_id" id="categoria_id" class="w-full border rounded px-3 py-2" required onchange="toggleOpcionesPizza()">
                <option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>
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
                <label for="sabor_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.sabor') }}</label>
                <select name="sabor_id" id="sabor_id" class="w-full border rounded px-3 py-2">
                    <option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>
                    @foreach ($sabores['data'] ?? $sabores as $sabor)
                        <option value="{{ $sabor['id'] }}">{{ $sabor['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tamaño --}}
            <div>
                <label for="tamano_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.tamano') }}</label>
                <select name="tamano_id" id="tamano_id" class="w-full border rounded px-3 py-2">
                    <option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>
                    @foreach ($tamanos as $tamano)
                        <option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Estado --}}
        <div>
            <label for="estado" class="block font-semibold">{{ __('viewAdmin/productos_admin.create.estado') }}</label>
            <select name="estado" id="estado" class="w-full border rounded px-3 py-2">
                <option value="1">{{ __('viewAdmin/productos_admin.create.activo') }}</option>
                <option value="0">{{ __('viewAdmin/productos_admin.create.inactivo') }}</option>
            </select>
        </div>

        {{-- Botón --}}
        <div class="pt-4">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded">
                {{ __('viewAdmin/productos_admin.create.guardar') }}
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






