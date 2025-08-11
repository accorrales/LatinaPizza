@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-6 text-center text-red-600">Seleccioná tu sucursal más cercana</h2>

    <div id="sucursales-lista" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // ✅ 1. Obtener ubicación actual
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // ✅ 2. Obtener sucursales desde backend
            const response = await fetch('http://127.0.0.1:8001/api/sucursales');
            const sucursales = await response.json();

            // ✅ 3. Calcular distancias y ordenar
            sucursales.forEach(s => {
                s.distancia = calcularDistancia(lat, lng, s.latitud, s.longitud);
            });

            sucursales.sort((a, b) => a.distancia - b.distancia);

            // ✅ 4. Mostrar sucursales
            const lista = document.getElementById('sucursales-lista');
            sucursales.forEach(s => {
                const card = document.createElement('div');
                card.className = 'bg-white shadow rounded-lg p-4';
                card.innerHTML = `
                    <h3 class="text-xl font-semibold text-gray-800">${s.nombre}</h3>
                    <p class="text-gray-600 mb-2">${s.direccion}</p>
                    <p class="text-sm text-gray-500 mb-4">Distancia: ${s.distancia.toFixed(2)} km</p>
                    <button onclick="seleccionarSucursal(${s.id})"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                        Seleccionar esta sucursal
                    </button>
                `;
                lista.appendChild(card);
            });

        }, (err) => {
            alert("No pudimos obtener tu ubicación.");
        });
    } else {
        alert("Tu navegador no permite obtener ubicación.");
    }
});

// 🔁 Función para calcular distancia con fórmula de Haversine
function calcularDistancia(lat1, lon1, lat2, lon2) {
    function toRad(x) { return x * Math.PI / 180; }
    const R = 6371; // km
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

// ✅ Guardar sucursal seleccionada y redirigir al menú
function seleccionarSucursal(id) {
    localStorage.setItem('sucursal_id', id);
    window.location.href = '/catalogo'; // o donde tengas el menú
}
</script>
@endsection
