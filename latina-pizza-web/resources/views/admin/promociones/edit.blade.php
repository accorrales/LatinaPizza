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
                <div class="componente border p-4 rounded mb-4 bg-gray-50" data-component>
                    <div class="flex justify-end">
                        <button type="button" class="text-sm text-red-700" data-remove>Eliminar componente</button>
                    </div>
                    <label class="block mb-2 font-semibold">{{ __('viewAdmin/promociones_admin.edit.tipo') }}</label>
                    <select name="componentes[{{ $i }}][tipo]" class="component-type w-full border rounded p-2" required>
                        <option value="pizza" {{ $componente['tipo'] == 'pizza' ? 'selected' : '' }}>
                            {{ __('viewAdmin/promociones_admin.edit.tipo_pizza') }}
                        </option>
                        <option value="bebida" {{ $componente['tipo'] == 'bebida' ? 'selected' : '' }}>
                            {{ __('viewAdmin/promociones_admin.edit.tipo_bebida') }}
                        </option>
                    </select>

                    <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.edit.cantidad') }}</label>
                    <input type="number" name="componentes[{{ $i }}][cantidad]" value="{{ $componente['cantidad'] }}" min="1" class="w-full border rounded p-2" required>

                    <div data-pizza-fields @class(['hidden' => $componente['tipo'] === 'bebida'])>
                        <label class="block mt-4 font-semibold">{{ __('viewAdmin/promociones_admin.edit.tamano') }}</label>
                        <select name="componentes[{{ $i }}][tamano_id]" class="w-full border rounded p-2">
                            <option value="">{{ __('viewAdmin/promociones_admin.edit.seleccionar_tamano') }}</option>
                            @foreach ($tamanos as $tamano)
                                <option value="{{ $tamano['id'] }}" {{ ($componente['tamano_id'] ?? null) == $tamano['id'] ? 'selected' : '' }}>
                                    {{ $tamano['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div data-drink-fields @class(['hidden' => $componente['tipo'] !== 'bebida'])>
                        <label class="block mt-4 font-semibold">Bebida</label>
                        <select name="componentes[{{ $i }}][producto_id]" class="w-full border rounded p-2">
                            <option value="">Seleccione una bebida</option>
                            @foreach ($bebidas as $bebida)
                                <option value="{{ $bebida['id'] }}" {{ ($componente['producto_id'] ?? null) == $bebida['id'] ? 'selected' : '' }}>
                                    {{ $bebida['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="agregar-componente" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Agregar componente
        </button>

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

@push('scripts')
<script>
    const promoSizes = @json($tamanos);
    const promoDrinks = @json($bebidas);
    const escapePromoHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
    const promoId = value => Number.isInteger(Number(value)) && Number(value) > 0 ? Number(value) : 0;
    let promoComponentIndex = {{ count($promocion['componentes']) }};

    function togglePromoComponent(select) {
        const component = select.closest('[data-component]');
        const isDrink = select.value === 'bebida';
        component.querySelector('[data-pizza-fields]').classList.toggle('hidden', isDrink);
        component.querySelector('[data-drink-fields]').classList.toggle('hidden', !isDrink);
    }

    document.getElementById('componentes').addEventListener('change', event => {
        if (event.target.matches('.component-type')) togglePromoComponent(event.target);
    });
    document.getElementById('componentes').addEventListener('click', event => {
        if (event.target.matches('[data-remove]')) event.target.closest('[data-component]').remove();
    });
    document.getElementById('agregar-componente').addEventListener('click', () => {
        const index = promoComponentIndex++;
        const sizes = promoSizes.map(item => `<option value="${promoId(item.id)}">${escapePromoHtml(item.nombre)}</option>`).join('');
        const drinks = promoDrinks.map(item => `<option value="${promoId(item.id)}">${escapePromoHtml(item.nombre)}</option>`).join('');
        document.getElementById('componentes').insertAdjacentHTML('beforeend', `
            <div class="componente border p-4 rounded mb-4 bg-gray-50" data-component>
                <div class="flex justify-end"><button type="button" class="text-sm text-red-700" data-remove>Eliminar componente</button></div>
                <label class="block mb-2 font-semibold">Tipo</label>
                <select name="componentes[${index}][tipo]" class="component-type w-full border rounded p-2" required>
                    <option value="pizza">Pizza</option><option value="bebida">Bebida</option>
                </select>
                <label class="block mt-4 font-semibold">Cantidad</label>
                <input type="number" name="componentes[${index}][cantidad]" value="1" min="1" class="w-full border rounded p-2" required>
                <div data-pizza-fields>
                    <label class="block mt-4 font-semibold">Tamaño</label>
                    <select name="componentes[${index}][tamano_id]" class="w-full border rounded p-2"><option value="">Seleccione</option>${sizes}</select>
                </div>
                <div data-drink-fields class="hidden">
                    <label class="block mt-4 font-semibold">Bebida</label>
                    <select name="componentes[${index}][producto_id]" class="w-full border rounded p-2"><option value="">Seleccione</option>${drinks}</select>
                </div>
            </div>`);
    });
</script>
@endpush
