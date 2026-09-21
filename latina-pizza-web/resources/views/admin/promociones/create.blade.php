@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow" data-promotion-editor data-next-index="0">
    <h1 class="text-2xl font-bold mb-6">{{ __('viewAdmin/promociones_admin.create.titulo') }}</h1>

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.promociones.store') }}" method="POST" data-show-loading>
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-2">{{ __('viewAdmin/promociones_admin.create.nombre') }}</label>
            <input type="text" name="nombre" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-2">{{ __('viewAdmin/promociones_admin.create.descripcion') }}</label>
            <textarea name="descripcion" class="w-full border rounded p-2"></textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-2">{{ __('viewAdmin/promociones_admin.create.precio_total') }}</label>
            <input type="number" name="precio_total" step="0.01" min="0" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-2">{{ __('viewAdmin/promociones_admin.create.precio_sugerido') }}</label>
            <input type="number" name="precio_sugerido" step="0.01" min="0" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-2">{{ __('viewAdmin/promociones_admin.create.imagen') }}</label>
            <input type="text" name="imagen" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="incluye_bebida" value="1" class="form-checkbox">
                <span class="ml-2">{{ __('viewAdmin/promociones_admin.create.incluye_bebida') }}</span>
            </label>
        </div>

        <hr class="my-6">
        <h2 class="text-xl font-semibold mb-4">{{ __('viewAdmin/promociones_admin.create.componentes_titulo') }}</h2>
        <div id="componentes"></div>
        <button type="button" id="agregar-componente" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            {{ __('viewAdmin/promociones_admin.create.agregar_componente') }}
        </button>

        <hr class="my-6">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            {{ __('viewAdmin/promociones_admin.create.guardar') }}
        </button>
    </form>

    <template id="promotion-component-template">
        <div class="componente border p-4 rounded mb-4 bg-gray-100" data-component>
            <div class="flex justify-end"><button type="button" class="text-sm text-red-700" data-remove>Eliminar componente</button></div>
            <label class="block mb-2 font-semibold">{{ __('viewAdmin/promociones_admin.create.tipo') }}</label>
            <select name="componentes[__INDEX__][tipo]" class="component-type w-full border rounded p-2" required>
                <option value="pizza">{{ __('viewAdmin/promociones_admin.create.tipo_pizza') }}</option>
                <option value="bebida">{{ __('viewAdmin/promociones_admin.create.tipo_bebida') }}</option>
            </select>
            <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.create.cantidad') }}</label>
            <input type="number" name="componentes[__INDEX__][cantidad]" value="1" min="1" class="w-full border rounded p-2" required>
            <div data-pizza-fields>
                <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.create.tamano') }}</label>
                <select name="componentes[__INDEX__][tamano_id]" class="w-full border rounded p-2">
                    <option value="">{{ __('viewAdmin/promociones_admin.create.seleccionar_tamano') }}</option>
                    @foreach($tamanos as $tamano)<option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>@endforeach
                </select>
            </div>
            <div data-drink-fields class="hidden">
                <label class="block mt-4 font-semibold">Bebida</label>
                <select name="componentes[__INDEX__][producto_id]" class="w-full border rounded p-2">
                    <option value="">Seleccione una bebida</option>
                    @foreach($bebidas as $bebida)<option value="{{ $bebida['id'] }}">{{ $bebida['nombre'] }}</option>@endforeach
                </select>
            </div>
        </div>
    </template>
</div>
@endsection
