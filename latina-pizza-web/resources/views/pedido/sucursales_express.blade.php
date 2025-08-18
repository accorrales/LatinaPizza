@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Título --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl md:text-3xl font-extrabold text-red-600">Elige la sucursal (Express)</h2>
        <a href="{{ route('express.index') }}" class="text-sm md:text-base text-gray-600 hover:text-gray-900 underline">
            ← Cambiar dirección
        </a>
    </div>

    {{-- Flash --}}
    @if(session('ok'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- Resumen de dirección --}}
    <div class="bg-white rounded-2xl shadow p-5 mb-6">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full bg-red-600/10 grid place-items-center">
                <span class="text-red-600 font-bold">📍</span>
            </div>
            <div class="grow">
                <div class="font-semibold text-lg">{{ $direccion['nombre'] ?? 'Dirección' }}</div>
                <div class="text-gray-700">
                    {{ $direccion['direccion_exacta'] ?? '' }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ $direccion['distrito'] ?? '' }}, {{ $direccion['canton'] ?? '' }}, {{ $direccion['provincia'] ?? '' }}
                </div>
                @if(!empty($direccion['telefono_contacto']))
                    <div class="text-sm text-gray-500 mt-1">Tel: {{ $direccion['telefono_contacto'] }}</div>
                @endif
                @if(!empty($direccion['referencias']))
                    <div class="text-xs text-gray-400 mt-1">Ref: {{ $direccion['referencias'] }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mapa --}}
    @php
        // valores de backend (opcionales)
        $maxKm    = $max_km    ?? 10;
        $currency = $currency  ?? '₡';
        $hasCoordsDireccion = !empty($direccion['latitud']) && !empty($direccion['longitud']);
        $sucsConCoords = collect($sucursales)->filter(fn($s) => !empty($s['latitud']) && !empty($s['longitud']))->values();
    @endphp

    <div class="bg-white rounded-2xl shadow p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xl font-semibold">Mapa</h3>
            <span class="text-sm text-gray-500">Cobertura: {{ $maxKm }} km</span>
        </div>

        @if($hasCoordsDireccion || $sucsConCoords->count())
            <div id="map" style="height: 360px; border-radius: 14px; overflow: hidden;"></div>
        @else
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800">
                No hay coordenadas para mostrar. Verifica que la dirección y sucursales tengan <em>latitud</em>/<em>longitud</em>.
            </div>
        @endif
    </div>

    {{-- Sucursales --}}
    @php
        $hayCobertura = collect($sucursales)->where('covered', true)->count() > 0;
    @endphp

    <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold">Sucursales disponibles</h3>
            <span class="text-sm text-gray-500">
                {{ !empty($sucursales) ? count($sucursales) : 0 }} encontradas
            </span>
        </div>

        @if(!$hayCobertura)
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800 mb-4">
                No hay sucursales que cubran la dirección seleccionada. Intenta con otra dirección más cercana.
            </div>
        @endif

        @if(empty($sucursales) || count($sucursales) === 0)
            <div class="p-4 rounded-xl bg-yellow-50 text-yellow-800">
                No hay sucursales registradas con coordenadas válidas.
            </div>
        @else
            <form method="POST" action="{{ route('sucursales.express.seleccionar') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="direccion_usuario_id" value="{{ $direccion['id'] }}">

                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($sucursales as $s)
                        @php
                            $covered = $s['covered'] ?? false;
                            $fee     = $s['delivery_fee'] ?? null;
                            $dist    = $s['distancia_km'] ?? null;
                        @endphp

                        <label class="border rounded-2xl p-4 flex gap-3 cursor-pointer hover:shadow transition-all {{ !$covered ? 'opacity-60' : '' }}">
                            <input type="radio" name="sucursal_id"
                                   value="{{ $s['id'] }}"
                                   class="mt-1"
                                   {{ !$covered ? 'disabled' : '' }}
                                   required>
                            <div class="grow">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-semibold text-lg">
                                        {{ $s['nombre'] ?? ('Sucursal #'.$s['id']) }}
                                    </div>

                                    @if($dist !== null)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                            {{ $dist }} km
                                        </span>
                                    @endif

                                    @if($covered && $fee !== null)
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                            Delivery: {{ $currency }}{{ number_format($fee, 0, '.', ',') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">
                                            Fuera de cobertura
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($s['direccion']))
                                    <div class="text-gray-700">{{ $s['direccion'] }}</div>
                                @endif
                                <div class="text-sm text-gray-500">
                                    @if(!empty($s['telefono'])) Tel: {{ $s['telefono'] }} @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="pt-2">
                    <button class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold"
                            {{ !$hayCobertura ? 'disabled class=opacity-60 cursor-not-allowed px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold' : '' }}>
                        Usar esta sucursal
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('styles')
  <link href="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.css" rel="stylesheet" />
  <style>
    /* contenedor responsivo con aspect-ratio */
    #map { aspect-ratio: 16 / 9; width: 100%; min-height: 320px; border-radius: 14px; overflow: hidden; }
    @media (min-width: 768px){ #map { min-height: 380px; } }
    @media (min-width:1024px){ #map { min-height: 420px; } }
  </style>
@endpush

@push('scripts')
  <script src="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.js"></script>
  <script src="https://unpkg.com/@turf/turf/turf.min.js"></script>
  <script>
  (function() {
    const el = document.getElementById('map');
    if (!el) return;

    const direccion  = @json($direccion ?? []);
    const sucursales = @json($sucursales ?? []);
    const maxKm      = Number(@json($max_km ?? 10)) || 10;
    const currency   = @json($currency ?? '₡');
    const apiKey     = @json(config('services.maptiler.key'));

    if (!apiKey) {
      el.innerHTML = '<div class="p-4 text-red-700 bg-red-50 rounded-xl">Falta MAPTILER_KEY en el frontend (.env)</div>';
      return;
    }

    // Evita re-inicializar en navegaciones
    if (window.__ml_map) {
      window.__ml_map.remove();
      window.__ml_map = null;
    }

    const style = `https://api.maptiler.com/maps/streets-v2/style.json?key=${apiKey}`;
    const map = window.__ml_map = new maplibregl.Map({
      container: el,
      style,
      center: [-84.09, 9.936], // San José CR
      zoom: 12,
      attributionControl: true
    });

    map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');

    // ---------- Normalización robusta de coordenadas ----------
    const toNum = v => (v===null || v===undefined || v==='') ? null : Number(v);

    function normalizePair(lat, lng) {
      let la = toNum(lat);
      let lo = toNum(lng);
      if (la === null || lo === null) return [null, null];

      // Longitud 0..360 → −180..180
      if (lo > 180) lo = lo - 360;

      // Heurística: si parecen invertidos (en CR |lon|~84 > |lat|~9) o lat fuera de rango
      const looksSwapped =
        (Math.abs(la) > 90 && Math.abs(lo) <= 90) ||
        (Math.abs(lo) < 20 && Math.abs(la) > 20);

      if (looksSwapped) { const tmp = la; la = lo; lo = tmp; }

      // Clamp por seguridad
      if (la > 90) la = 90; if (la < -90) la = -90;
      if (lo > 180) lo = 180; if (lo < -180) lo = -180;

      return [la, lo];
    }

    const hasLatLng = (obj) => {
      const [la, lo] = normalizePair(obj?.latitud, obj?.longitud);
      return la !== null && lo !== null;
    };
    // -----------------------------------------------------------

    const bounds = new maplibregl.LngLatBounds();

    function addMarker(lat, lng, html, color='#d11') {
      const m = document.createElement('div');
      m.style.width='24px'; m.style.height='24px';
      m.style.borderRadius='50%';
      m.style.background=color; m.style.boxShadow='0 0 0 2px #fff';
      new maplibregl.Marker({ element: m, anchor:'bottom' })
        .setLngLat([lng, lat])
        .setPopup(new maplibregl.Popup({ offset: 18 }).setHTML(html))
        .addTo(map);
      bounds.extend([lng, lat]);
    }

    function markerHtml(title, addr, extras='') {
      return `<strong>${title}</strong><br>${addr || ''}${extras ? '<br>'+extras : ''}`;
    }

    map.on('load', () => {
      // Dirección (normalizada)
      if (hasLatLng(direccion)) {
        const [lat, lng] = normalizePair(direccion.latitud, direccion.longitud);
        addMarker(lat, lng, markerHtml(direccion.nombre ?? 'Dirección', direccion.direccion_exacta ?? ''), '#2b6cb0');
      }

      // Sucursales (normalizadas)
      const validas = (sucursales || []).filter(s => hasLatLng(s));
      validas.forEach(s => {
        const [lat, lng] = normalizePair(s.latitud, s.longitud);
        const dist = (s.distancia_km ?? '') !== '' ? `${s.distancia_km} km` : '';
        const fee  = (s.delivery_fee ?? '') !== '' ? ` • Delivery: ${currency}${s.delivery_fee}` : '';
        const cover= (s.covered === false) ? ' • <span style="color:#b91c1c;">Fuera de cobertura</span>' : '';
        addMarker(lat, lng, markerHtml(s.nombre ?? ('Sucursal #'+s.id), s.direccion ?? '', `${dist}${fee}${cover}`), '#d11');
      });

      // Fit a lo que haya
      if (!bounds.isEmpty()) {
        map.fitBounds(bounds, { padding: 40, maxZoom: 16, duration: 500 });
      }

      // Círculo de cobertura SOLO sobre la sucursal seleccionada
      const circleSourceId = 'coverage-source';
      const circleLayerId  = 'coverage-layer';

      function drawCoverage(lat, lng) {
        const [la, lo] = normalizePair(lat, lng);
        if (la === null || lo === null) return;

        const circle = turf.circle([lo, la], maxKm, { steps: 64, units: 'kilometers' });

        if (map.getLayer(circleLayerId)) map.removeLayer(circleLayerId);
        if (map.getSource(circleSourceId)) map.removeSource(circleSourceId);

        map.addSource(circleSourceId, { type: 'geojson', data: circle });
        map.addLayer({
          id: circleLayerId,
          type: 'fill',
          source: circleSourceId,
          paint: {
            'fill-color': '#ef4444',
            'fill-opacity': 0.12
          }
        });
      }

      // Selección inicial: marcada o la más cercana cubierta
      function selectInitial() {
        const checked = document.querySelector('input[name="sucursal_id"]:checked');
        if (checked) {
          const id = Number(checked.value);
          const sel = validas.find(s => Number(s.id) === id);
          if (sel) {
            const [la, lo] = normalizePair(sel.latitud, sel.longitud);
            drawCoverage(la, lo);
          }
          return;
        }
        const covered = validas.filter(s => s.covered)
          .sort((a,b)=>(a.distancia_km??9999)-(b.distancia_km??9999));
        if (covered.length) {
          const near = covered[0];
          const [la, lo] = normalizePair(near.latitud, near.longitud);
          drawCoverage(la, lo);
          const radio = document.querySelector(`input[name="sucursal_id"][value="${near.id}"]`);
          if (radio && !radio.disabled) radio.checked = true;
        }
      }
      selectInitial();

      // Al cambiar de sucursal, rehacer el círculo
      document.querySelectorAll('input[name="sucursal_id"]').forEach(r => {
        r.addEventListener('change', () => {
          const id = Number(r.value);
          const sel = validas.find(s => Number(s.id) === id);
          if (sel) {
            const [la, lo] = normalizePair(sel.latitud, sel.longitud);
            drawCoverage(la, lo);
          }
        });
      });

      // Responsivo real
      if (window.ResizeObserver) {
        new ResizeObserver(() => map.resize()).observe(el);
      } else {
        window.addEventListener('resize', () => map.resize());
      }
    });
  })();
  </script>
@endpush

