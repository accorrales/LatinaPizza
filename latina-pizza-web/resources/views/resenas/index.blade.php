@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">Reseñas del producto</h2>

    {{-- Mostrar reseñas existentes --}}
    @forelse ($resenas as $resena)
        <div class="bg-white p-4 rounded shadow mb-4">
            <div class="flex justify-between items-center">
                <p class="text-gray-800 font-semibold">
                    {{ $resena['user']['name'] ?? 'Usuario' }}
                </p>
                <p class="text-yellow-500">
                    ⭐ {{ $resena['calificacion'] }} / 5
                </p>
            </div>
            <p class="text-gray-600 mt-2">{{ $resena['comentario'] }}</p>
            <p class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($resena['created_at'])->diffForHumans() }}</p>
            @auth
                @if ($resena['user']['id'] == auth()->id())
                    <form action="{{ route('resenas.destroy', $resena['id']) }}" method="POST" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('¿Eliminar tu reseña?')" class="text-red-500 text-sm hover:underline">
                            🗑️ Eliminar reseña
                        </button>
                    </form>
                @endif
            @endauth
        </div>
    @empty
        <p class="text-gray-600">Aún no hay reseñas para este producto.</p>
    @endforelse

    {{-- Formulario para crear reseña (solo si está logueado) --}}
    @auth
        <div class="mt-8">
            <h3 class="text-xl font-semibold mb-4">Escribe tu reseña</h3>
            <form action="{{ route('resenas.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="sabor_id" value="{{ $saborId }}">

                {{-- Calificación (estrellas del 1 al 5) --}}
                <label for="calificacion" class="block font-semibold">Calificación:</label>
                <select name="calificacion" id="calificacion" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <option value="">Selecciona una calificación</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }} estrella{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>

                {{-- Comentario --}}
                <label for="comentario" class="block font-semibold">Comentario:</label>
                <textarea name="comentario" id="comentario" rows="4"
                          class="w-full border border-gray-300 rounded px-3 py-2"
                          placeholder="Escribe tu opinión sobre este sabor..."></textarea>

                <div class="text-right">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded">
                        Enviar reseña
                    </button>
                </div>
            </form>
        </div>
    @else
        <p class="mt-6 text-center text-gray-500">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                Inicia sesión para escribir una reseña
            </a>
        </p>
    @endauth
</div>
@endsection
