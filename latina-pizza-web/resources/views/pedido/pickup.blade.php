@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
  <h2 class="text-3xl font-bold mb-6 text-center text-red-600">Seleccioná tu sucursal más cercana</h2>

  @if (session('error'))
    <div class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800">{{ session('error') }}</div>
  @endif
  @if (session('ok'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('ok') }}</div>
  @endif

  <div id="sucursales-lista" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($sucursales as $s)
      <div class="bg-white shadow rounded-lg p-4 border">
        <h3 class="text-xl font-semibold text-gray-800">{{ $s['nombre'] }}</h3>
        <p class="text-gray-600 mb-2">{{ $s['direccion'] ?? '' }}</p>
        <p class="text-sm text-gray-500 mb-4">
          Distancia: <span class="dist-valor" data-lat="{{ $s['latitud'] }}" data-lng="{{ $s['longitud'] }}">—</span>
        </p>

        <form method="POST" action="{{ route('pickup.seleccionar') }}">
          @csrf
          <input type="hidden" name="sucursal_id" value="{{ $s['id'] }}">
          <button type="submit"
            class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
            Elegir esta sucursal
          </button>
        </form>
      </div>
    @empty
      <div class="col-span-full">
        <div class="p-4 bg-gray-50 rounded border text-gray-700">No hay sucursales disponibles.</div>
      </div>
    @endforelse
  </div>
</div>

{{-- Orden por distancia en el cliente (opcional, solo UI) --}}
<script>
(function () {
  const toRad = x => x * Math.PI / 180;
  const km = (lat1, lon1, lat2, lon2) => {
    const R = 6371, dLa = toRad(lat2-lat1), dLo = toRad(lon2-lon1);
    const a = Math.sin(dLa/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLo/2)**2;
    return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
  };

  if (!navigator.geolocation) return;

  navigator.geolocation.getCurrentPosition((pos) => {
    const uLat = pos.coords.latitude, uLng = pos.coords.longitude;
    const cards = Array.from(document.querySelectorAll('#sucursales-lista > div'));
    cards.forEach(card => {
      const span = card.querySelector('.dist-valor');
      const lat = parseFloat(span.dataset.lat), lng = parseFloat(span.dataset.lng);
      if (isFinite(lat) && isFinite(lng)) {
        const d = km(uLat,uLng,lat,lng);
        span.textContent = d.toFixed(2) + ' km';
        card.dataset.dist = d;
      } else {
        card.dataset.dist = 1e9;
      }
    });
    const container = document.getElementById('sucursales-lista');
    cards.sort((a,b) => (+a.dataset.dist) - (+b.dataset.dist))
         .forEach(c => container.appendChild(c));
  }, () => {}, {enableHighAccuracy:true, timeout:8000});
})();
</script>
@endsection

