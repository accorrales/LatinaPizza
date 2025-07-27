@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">Crear Promoción</h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.promociones.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold">Nombre de la promoción</label>
            <input type="text" name="nombre" class="w-full border rounded p-2" value="{{ old('nombre') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Descripción</label>
            <textarea name="descripcion" class="w-full border rounded p-2">{{ old('descripcion') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Precio Total</label>
            <input type="number" name="precio_total" step="0.01" class="w-full border rounded p-2" value="{{ old('precio_total') }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Precio Sugerido (opcional)</label>
            <input type="number" name="precio_sugerido" step="0.01" class="w-full border rounded p-2" value="{{ old('precio_sugerido') }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Imagen (URL opcional)</label>
            <input type="text" name="imagen" class="w-full border rounded p-2" value="{{ old('imagen') }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">¿Incluye bebida?</label>
            <input type="checkbox" name="incluye_bebida" value="1" {{ old('incluye_bebida') ? 'checked' : '' }}>
        </div>

        <hr class="my-6">

        <h3 class="text-xl font-semibold mb-2">Componentes de la Promoción</h3>

        <div id="componentes">
            <div class="componente border p-4 rounded mb-4 bg-gray-50">
                <label class="block mb-2 font-semibold">Tipo de componente</label>
                <select name="componentes[0][tipo]" class="w-full border rounded p-2" required>
                    <option value="pizza">Pizza</option>
                    <option value="bebida">Bebida</option>
                </select>

                <label class="block mt-4 font-semibold">Cantidad</label>
                <input type="number" name="componentes[0][cantidad]" value="1" min="1" class="w-full border rounded p-2" required>

                <label class="block mt-4 font-semibold">Tamaño (solo aplica para pizzas)</label>
                <select name="componentes[0][tamano_id]" class="w-full border rounded p-2">
                    <option value="">Seleccionar tamaño</option>
                    @foreach ($tamanos as $tamano)
                        <option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="button" id="agregar-componente" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Agregar componente</button>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Guardar Promoción</button>
            <a href="{{ route('admin.promociones.index') }}" class="ml-4 text-gray-600 hover:underline">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM listo'); // Para pruebas

        let contador = 1;

        const botonAgregar = document.getElementById('agregar-componente');
        const contenedor = document.getElementById('componentes');

        if (!botonAgregar || !contenedor) {
            console.warn('No se encontró el botón o el contenedor');
            return;
        }

        botonAgregar.addEventListener('click', () => {
            const template = `
                <div class="componente border p-4 rounded mb-4 bg-gray-100">
                    <label class="block mb-2 font-semibold">Tipo de componente</label>
                    <select name="componentes[${contador}][tipo]" class="w-full border rounded p-2" required>
                        <option value="pizza">Pizza</option>
                        <option value="bebida">Bebida</option>
                    </select>

                    <label class="block mt-4 font-semibold">Cantidad</label>
                    <input type="number" name="componentes[${contador}][cantidad]" value="1" min="1" class="w-full border rounded p-2" required>

                    <label class="block mt-4 font-semibold">Tamaño (solo aplica para pizzas)</label>
                    <select name="componentes[${contador}][tamano_id]" class="w-full border rounded p-2">
                        <option value="">Seleccionar tamaño</option>
                        @foreach ($tamanos as $tamano)
                            <option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
            `;

            contenedor.insertAdjacentHTML('beforeend', template);
            contador++;
        });
    });
</script>
@endpush