@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Crear nueva promoción</h1>

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.promociones.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-2">Nombre</label>
            <input type="text" name="nombre" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Descripción</label>
            <textarea name="descripcion" class="w-full border rounded p-2"></textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Precio Total</label>
            <input type="number" name="precio_total" step="0.01" min="0" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Precio Sugerido (opcional)</label>
            <input type="number" name="precio_sugerido" step="0.01" min="0" class="w-full border rounded p-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Imagen (URL)</label>
            <input type="text" name="imagen" class="w-full border rounded p-2">
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="incluye_bebida" value="1" class="form-checkbox">
                <span class="ml-2">Incluye bebida</span>
            </label>
        </div>

        <hr class="my-6">

        <h2 class="text-xl font-semibold mb-4">Componentes</h2>
        <div id="componentes"></div>

        <button type="button" id="agregar-componente" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            + Agregar componente
        </button>

        <hr class="my-6">

        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            Guardar promoción
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const tamanos = @json($tamanos);
    let contador = 1;

    document.getElementById('agregar-componente').addEventListener('click', function () {
        const opciones = tamanos.map(t => `<option value="${t.id}">${t.nombre}</option>`).join('');

        const html = `
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
                ${opciones}
            </select>
        </div>`;
        
        document.getElementById('componentes').insertAdjacentHTML('beforeend', html);
        contador++;
    });
</script>
@endpush