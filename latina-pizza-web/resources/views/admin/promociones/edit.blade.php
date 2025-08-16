@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">{{ __('viewAdmin/promociones_admin.edit.titulo') }}</h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.promociones.update', $promocion['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.nombre') }}</label>
            <input type="text" name="nombre" class="w-full border rounded p-2" value="{{ old('nombre', $promocion['nombre']) }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.descripcion') }}</label>
            <textarea name="descripcion" class="w-full border rounded p-2">{{ old('descripcion', $promocion['descripcion']) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.precio_total') }}</label>
            <input type="number" name="precio_total" step="0.01" class="w-full border rounded p-2" value="{{ old('precio_total', $promocion['precio_total']) }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.precio_sugerido') }}</label>
            <input type="number" name="precio_sugerido" step="0.01" class="w-full border rounded p-2" value="{{ old('precio_sugerido', $promocion['precio_sugerido']) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.imagen') }}</label>
            <input type="text" name="imagen" class="w-full border rounded p-2" value="{{ old('imagen', $promocion['imagen']) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">{{ __('viewAdmin/promociones_admin.edit.incluye_bebida') }}</label>
            <input type="checkbox" name="incluye_bebida" value="1" {{ old('incluye_bebida', $promocion['incluye_bebida']) ? 'checked' : '' }}>
        </div>

        <hr class="my-6">
        <h3 class="text-xl font-semibold mb-2">{{ __('viewAdmin/promociones_admin.edit.componentes_titulo') }}</h3>

        <div id="componentes">
            @foreach ($promocion['componentes'] as $i => $componente)
                <div class="componente border p-4 rounded mb-4 bg-gray-50">
                    <label class="block mb-2 font-semibold">{{ __('viewAdmin/promociones_admin.edit.tipo') }}</label>
                    <select name="componentes[{{ $i }}][tipo]" class="w-full border rounded p-2" required>
                        <option value="pizza" {{ $componente['tipo'] == 'pizza' ? 'selected' : '' }}>
                            {{ __('viewAdmin/promociones_admin.edit.tipo_pizza') }}
                        </option>
                        <option value="bebida" {{ $componente['tipo'] == 'bebida' ? 'selected' : '' }}>
                            {{ __('viewAdmin/promociones_admin.edit.tipo_bebida') }}
                        </option>
                    </select>

                    <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.edit.cantidad') }}</label>
                    <input type="number" name="componentes[{{ $i }}][cantidad]" value="{{ $componente['cantidad'] }}" min="1" class="w-full border rounded p-2" required>

                    <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.edit.tamano') }}</label>
                    <select name="componentes[{{ $i }}][tamano_id]" class="w-full border rounded p-2">
                        <option value="">{{ __('viewAdmin/promociones_admin.edit.seleccionar_tamano') }}</option>
                        @foreach ($tamanos as $tamano)
                            <option value="{{ $tamano['id'] }}" {{ $componente['tamano_id'] == $tamano['id'] ? 'selected' : '' }}>
                                {{ $tamano['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                {{ __('viewAdmin/promociones_admin.edit.actualizar') }}
            </button>
            <a href="{{ route('admin.promociones.index') }}" class="ml-4 text-gray-600 hover:underline">
                {{ __('viewAdmin/promociones_admin.edit.cancelar') }}
            </a>
        </div>
    </form>
</div>
@endsection
