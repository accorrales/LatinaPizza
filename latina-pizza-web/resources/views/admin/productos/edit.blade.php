@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h2 class="text-2xl font-bold mb-6">{{ __('viewAdmin/productos_admin.edit.titulo') }}</h2>

    <form action="{{ route('admin.productos.update', $producto['id']) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Campo oculto para enviar la categoría aunque el select esté bloqueado --}}
        <input type="hidden" name="categoria_id" value="{{ $producto['categoria_id'] }}">

        {{-- Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.nombre') }}</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2"
                   value="{{ $producto['nombre'] }}" 
                   @if(strtolower($producto['categoria']['nombre']) === 'pizza') readonly @endif>
        </div>

        {{-- Descripción --}}
        <div>
            <label for="descripcion" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.descripcion') }}</label>
            <textarea name="descripcion" id="descripcion" class="w-full border rounded px-3 py-2"
                      @if(strtolower($producto['categoria']['nombre']) === 'pizza') readonly @endif>{{ $producto['descripcion'] }}</textarea>
        </div>

        {{-- Precio --}}
        <div>
            <label for="precio" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.precio') }}</label>
            <input type="number" name="precio" id="precio" class="w-full border rounded px-3 py-2" step="0.01"
                   value="{{ $producto['precio'] }}" required>
        </div>

        {{-- Imagen --}}
        <div>
            <label for="imagen" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.imagen') }}</label>
            <input type="text" name="imagen" id="imagen" class="w-full border rounded px-3 py-2"
                   value="{{ $producto['imagen'] }}">
        </div>

        {{-- Categoría (solo visual) --}}
        <div>
            <label class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.categoria') }}</label>
            <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                   value="{{ $producto['categoria']['nombre'] }}" disabled>
        </div>

        {{-- Si es Pizza, mostrar sabor y tamaño --}}
        @if(strtolower($producto['categoria']['nombre']) === 'pizza')
        <div class="space-y-4">
            {{-- Sabor --}}
            <div>
                <label for="sabor_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.sabor') }}</label>
                <select name="sabor_id" id="sabor_id" class="w-full border rounded px-3 py-2">
                    @foreach ($sabores['data'] ?? $sabores as $sabor)
                        <option value="{{ $sabor['id'] }}" 
                            @if($sabor['id'] == $producto['sabor_id']) selected @endif>
                            {{ $sabor['nombre'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tamaño --}}
            <div>
                <label for="tamano_id" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.tamano') }}</label>
                <select name="tamano_id" id="tamano_id" class="w-full border rounded px-3 py-2">
                    @foreach ($tamanos as $tamano)
                        <option value="{{ $tamano['id'] }}" 
                            @if($tamano['id'] == $producto['tamano_id']) selected @endif>
                            {{ $tamano['nombre'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        {{-- Estado --}}
        <div>
            <label for="estado" class="block font-semibold">{{ __('viewAdmin/productos_admin.edit.estado') }}</label>
            <select name="estado" id="estado" class="w-full border rounded px-3 py-2">
                <option value="1" @if($producto['estado']) selected @endif>{{ __('viewAdmin/productos_admin.edit.activo') }}</option>
                <option value="0" @if(!$producto['estado']) selected @endif>{{ __('viewAdmin/productos_admin.edit.inactivo') }}</option>
            </select>
        </div>

        {{-- Botón --}}
        <div class="pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                {{ __('viewAdmin/productos_admin.edit.guardar') }}
            </button>
        </div>
    </form>
</div>
@endsection




