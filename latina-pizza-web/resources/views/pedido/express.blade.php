@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-center text-red-600 mb-6">Entrega a domicilio (Express)</h2>

    @if(session('ok'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- 1) Seleccionar una dirección existente --}}
    <div class="bg-white rounded-2xl shadow p-5 mb-8">
        <h3 class="text-xl font-semibold mb-4">Mis direcciones</h3>

        @if(!empty($direcciones) && count($direcciones))
            <form method="POST" action="{{ route('express.seleccionar') }}" class="space-y-4">
                @csrf
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($direcciones as $d)
                        <label class="border rounded-xl p-4 flex gap-3 cursor-pointer hover:shadow transition">
                            <input type="radio" name="direccion_usuario_id" value="{{ $d['id'] }}" class="mt-1" required>
                            <div>
                                <div class="font-medium">{{ $d['nombre'] }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $d['provincia'] }} • {{ $d['canton'] }} • {{ $d['distrito'] }}
                                </div>
                                <div class="text-sm text-gray-600">{{ $d['direccion_exacta'] }}</div>
                                <div class="text-sm text-gray-500">Tel: {{ $d['telefono_contacto'] }}</div>
                                @if(!empty($d['referencias']))
                                    <div class="text-xs text-gray-400">Ref: {{ $d['referencias'] }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                <button class="px-5 py-2 rounded-xl bg-red-600 text-white font-semibold">
                    Continuar con esta dirección
                </button>
            </form>
        @else
            <p class="text-gray-600">Aún no tienes direcciones guardadas.</p>
        @endif
    </div>

    {{-- 2) Agregar nueva dirección --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="text-xl font-semibold mb-4">Agregar nueva dirección</h3>
        <form method="POST" action="{{ route('express.store') }}" class="grid md:grid-cols-2 gap-4" id="formNuevaDir">
            @csrf
            <input type="text" name="nombre" placeholder="Nombre (Casa, Trabajo…)" class="border rounded-lg p-2" required>
            <input type="text" name="telefono_contacto" placeholder="Teléfono" class="border rounded-lg p-2" required>

            <input type="text" name="provincia" placeholder="Provincia" class="border rounded-lg p-2" required>
            <input type="text" name="canton" placeholder="Cantón" class="border rounded-lg p-2" required>
            <input type="text" name="distrito" placeholder="Distrito" class="border rounded-lg p-2" required>

            <input type="text" name="direccion_exacta" placeholder="Dirección exacta" class="border rounded-lg p-2 md:col-span-2" required>
            <textarea name="referencias" placeholder="Referencias (opcional)" class="border rounded-lg p-2 md:col-span-2"></textarea>

            {{-- Geolocalización --}}
            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">
            <div class="md:col-span-2 flex items-center gap-3">
                <button type="button" id="btnUbicacion" class="px-4 py-2 rounded-lg border">Usar mi ubicación</button>
                <span id="geoStatus" class="text-sm text-gray-500"></span>
            </div>

            <div class="md:col-span-2">
                <button class="px-5 py-2 rounded-xl bg-gray-900 text-white font-semibold">
                    Guardar dirección
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JS geolocalización simple --}}
<script>
document.getElementById('btnUbicacion')?.addEventListener('click', function() {
    const status = document.getElementById('geoStatus');
    if (!navigator.geolocation) {
        status.textContent = 'Geolocalización no soportada en este navegador.';
        return;
    }
    status.textContent = 'Obteniendo ubicación…';
    navigator.geolocation.getCurrentPosition((pos) => {
        document.getElementById('latitud').value  = pos.coords.latitude.toFixed(6);
        document.getElementById('longitud').value = pos.coords.longitude.toFixed(6);
        status.textContent = 'Ubicación lista ✅';
    }, (err) => {
        status.textContent = 'No se pudo obtener la ubicación (' + err.message + ')';
    }, { enableHighAccuracy: true, timeout: 10000 });
});
</script>
@endsection
