@extends('layouts.app')

@section('content')
@php
    $isPizza = strtolower($producto['categoria']['nombre'] ?? '') === 'pizza';
@endphp

<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-6">{{ __('viewAdmin/productos_admin.edit.titulo') }}</h2>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.productos.update', $producto['id']) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <input type="hidden" name="categoria_id" value="{{ $producto['categoria_id'] }}">

        <div>
            <label for="nombre" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.nombre') }}</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2"
                   value="{{ old('nombre', $producto['nombre']) }}"
                   @if($isPizza) readonly @endif>
        </div>

        <div>
            <label for="descripcion" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.descripcion') }}</label>
            <textarea name="descripcion" id="descripcion" class="w-full border rounded px-3 py-2"
                      @if($isPizza) readonly @endif>{{ old('descripcion', $producto['descripcion']) }}</textarea>
        </div>

        <div>
            <label for="precio" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.precio') }}</label>
            <input type="number" name="precio" id="precio" class="w-full border rounded px-3 py-2 @if($isPizza) bg-gray-100 @endif" step="0.01"
                   value="{{ old('precio', $producto['precio']) }}" required @if($isPizza) readonly @endif>
            @if($isPizza)
                <p class="text-sm text-gray-600 mt-1">
                    El precio de las pizzas se calcula desde el precio base del tamaño.
                    <a href="{{ route('admin.tamanos.index') }}" class="text-blue-600 hover:underline">Editar precios en Tamaños</a>.
                </p>
            @endif
        </div>

        <div>
            <label for="imagen" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.imagen') }}</label>
            <input type="text" name="imagen" id="imagen" class="w-full border rounded px-3 py-2"
                   value="{{ old('imagen', $producto['imagen']) }}">
        </div>

        <div>
            <label class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.categoria') }}</label>
            <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
                   value="{{ $producto['categoria']['nombre'] }}" disabled>
        </div>

        @if($isPizza)
        <div class="space-y-4">
            <div>
                <label for="sabor_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.sabor') }}</label>
                <select name="sabor_id" id="sabor_id" class="w-full border rounded px-3 py-2">
                    @foreach ($sabores['data'] ?? $sabores as $sabor)
                        <option value="{{ $sabor['id'] }}"
                            @selected(old('sabor_id', $producto['sabor_id']) == $sabor['id'])>
                            {{ $sabor['nombre'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tamano_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.tamano') }}</label>
                <select name="tamano_id" id="tamano_id" class="w-full border rounded px-3 py-2">
                    @foreach ($tamanos as $tamano)
                        <option value="{{ $tamano['id'] }}"
                            @selected(old('tamano_id', $producto['tamano_id']) == $tamano['id'])>
                            {{ $tamano['nombre'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div>
            <label for="estado" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.estado') }}</label>
            <select name="estado" id="estado" class="w-full border rounded px-3 py-2">
                <option value="1" @selected((string) old('estado', $producto['estado'] ? '1' : '0') === '1')>{{ __('viewAdmin/productos_admin.edit.activo') }}</option>
                <option value="0" @selected((string) old('estado', $producto['estado'] ? '1' : '0') === '0')>{{ __('viewAdmin/productos_admin.edit.inactivo') }}</option>
            </select>
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                {{ __('viewAdmin/productos_admin.edit.guardar') }}
            </button>
        </div>
    </form>
</div>
@endsection
