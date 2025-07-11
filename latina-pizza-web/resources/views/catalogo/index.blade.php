@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-center text-red-600 mb-6">Menú Latina</h2>

    <!-- Filtros de categorías -->
    <div class="flex flex-wrap justify-center gap-3 mb-8">
        <a href="{{ route('catalogo.index') }}"
           class="px-4 py-2 rounded-full border transition
           {{ is_null($categoriaSeleccionada) ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
            Todos
        </a>
        @foreach ($categorias as $cat)
            <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}"
               class="px-4 py-2 rounded-full border transition
               {{ $categoriaSeleccionada == $cat['id'] ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                {{ $cat['nombre'] }}
            </a>
        @endforeach
    </div>

    <!-- Tarjetas de sabores -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($sabores as $sabor)
            @include('catalogo.partials.card', ['sabor' => $sabor])
        @endforeach
    </div>
    @include('catalogo.partials.modal')
</div>
@endsection

@section('scripts')
<script>
    const API_URL = "{{ config('app.api_url') }}";
</script>
<script>
let extrasData = [];
let precioBase = 0;
let precioMasa = 0;

window.abrirModal = async function(element) {
    const sabor = JSON.parse(element.dataset.sabor);

    precioBase = 0;
    precioMasa = 0;
    extrasData = [];

    document.getElementById('modalImagen').src = sabor.imagen;
    document.getElementById('modalNombre').textContent = sabor.sabor_nombre;
    document.getElementById('modalDescripcion').textContent = sabor.descripcion;

    // Tamaños
   const tamanosHtml = sabor.tamanos.map(t => `
        <label class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer text-sm text-gray-700">
            <input type="radio" name="producto_id" value="${t.producto_id}" data-precio="${t.precio_base}" onchange="cambiarTamano(${t.precio_base}, '${t.tamano_nombre.toLowerCase()}')" required>
            ${t.tamano_nombre} - ₡${parseFloat(t.precio_base).toFixed(2)}
        </label>
    `).join('');

    document.getElementById('modalTamanos').innerHTML = tamanosHtml;

    // Masas
    try {
        const res = await fetch(`http://127.0.0.1:8001/api/masas`);
        const masas = await res.json();
        const masaSelect = document.getElementById('masa');
        masaSelect.innerHTML = masas.map(m => `<option value="${m.id}" data-precio="${m.precio_extra}">${m.tipo} (+₡${m.precio_extra})</option>`).join('');
        masaSelect.onchange = function () {
            const precio = parseFloat(this.selectedOptions[0].dataset.precio);
            precioMasa = isNaN(precio) ? 0 : precio;
            actualizarTotal();
        };
    } catch {
        document.getElementById('masa').innerHTML = '<option>Error al cargar masas</option>';
    }

    // Extras
    try {
        const res = await fetch(`http://127.0.0.1:8001/api/extras`);
        extrasData = await res.json();
        renderizarExtras('precio_pequena'); // default
    } catch {
        document.getElementById('extrasOpciones').innerHTML = '<p class="text-xs text-red-500">No se pudieron cargar los extras.</p>';
    }

    document.getElementById('modalSabor').classList.remove('hidden');
};

function cambiarTamano(precio, tamanoNombre) {
    precioBase = parseFloat(precio);

    let key = 'precio_pequena';
    if (tamanoNombre.includes('mediana')) key = 'precio_mediana';
    else if (tamanoNombre.includes('grande') && !tamanoNombre.includes('extra')) key = 'precio_grande';
    else if (tamanoNombre.includes('extra')) key = 'precio_extragrande';

    renderizarExtras(key);
    actualizarTotal();
}

function renderizarExtras(clave) {
    const contenedor = document.getElementById('extrasOpciones');
    contenedor.innerHTML = extrasData.map(extra => {
        const precio = parseFloat(extra[clave]) || 0;
        return `
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="extras[]" value="${extra.id}" data-precio="${precio}" onchange="actualizarTotal()">
                ${extra.nombre} (+₡${precio.toFixed(0)})
            </label>
        `;
    }).join('');
}

function actualizarTotal() {
    const extras = document.querySelectorAll('input[name="extras[]"]:checked');
    let totalExtras = 0;
    extras.forEach(e => {
        totalExtras += parseFloat(e.dataset.precio);
    });

    const total = precioBase + precioMasa + totalExtras;
    document.getElementById('precioTotal').textContent = `Total: ₡${total.toFixed(2)}`;
    document.getElementById('inputPrecioTotal').value = total.toFixed(2);
}

function cerrarModal() {
    document.getElementById('modalSabor').classList.add('hidden');
}
</script>
@endsection

<style>
@keyframes fade-in-down {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }
</style>
