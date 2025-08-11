@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Ingrese su dirección de entrega</h1>

    <form onsubmit="guardarDireccion(event)" class="max-w-lg mx-auto bg-white shadow rounded-lg p-6 space-y-4">
        <div>
            <label class="block text-gray-700">Provincia:</label>
            <input type="text" id="provincia" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-gray-700">Cantón:</label>
            <input type="text" id="canton" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-gray-700">Distrito:</label>
            <input type="text" id="distrito" required class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-gray-700">Dirección exacta:</label>
            <textarea id="detalle" required class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <div>
            <label class="block text-gray-700">Teléfono de contacto:</label>
            <input type="tel" id="telefono" required class="w-full border rounded px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
            Confirmar dirección
        </button>
    </form>
</div>

<script>
    function guardarDireccion(event) {
        event.preventDefault();

        const direccion = {
            provincia: document.getElementById('provincia').value,
            canton: document.getElementById('canton').value,
            distrito: document.getElementById('distrito').value,
            detalle: document.getElementById('detalle').value,
            telefono: document.getElementById('telefono').value
        };

        localStorage.setItem('direccion_express', JSON.stringify(direccion));

        alert('Dirección guardada correctamente');
        window.location.href = '/catalogo'; // ir al menú después de guardar
    }
</script>
@endsection
