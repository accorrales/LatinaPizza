@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6">{{ __('resenas.titulo') }}</h2>

    {{-- Mostrar reseñas existentes --}}
    @forelse ($resenas as $resena)
        <div class="bg-white p-4 rounded shadow mb-4">
            <div class="flex justify-between items-center">
                <p class="text-gray-800 font-semibold">
                    {{ $resena['user']['name'] ?? __('resenas.usuario') }}
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
                        <button type="submit" onclick="return confirm('{{ __('resenas.confirmar_eliminar') }}')" class="text-red-500 text-sm hover:underline">
                            {{ __('resenas.eliminar_resena') }}
                        </button>
                    </form>
                @endif
            @endauth
        </div>
    @empty
        <p class="text-gray-600">{{ __('resenas.sin_resenas') }}</p>
    @endforelse

    {{-- Formulario para crear reseña (solo si está logueado) --}}
    @auth
        <div class="mt-8">
            <h3 class="text-xl font-semibold mb-4">{{ __('resenas.escribe_resena') }}</h3>
            <form action="{{ route('resenas.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="sabor_id" value="{{ $saborId }}">

                {{-- Calificación --}}
                <label for="calificacion" class="block font-semibold">{{ __('resenas.calificacion') }}</label>
                <select name="calificacion" id="calificacion" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <option value="">{{ __('resenas.selecciona_calificacion') }}</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }} {{ trans_choice('resenas.estrellas', $i) }}</option>
                    @endfor
                </select>

                {{-- Comentario --}}
                <label for="comentario" class="block font-semibold">{{ __('resenas.comentario') }}</label>
                <textarea name="comentario" id="comentario" rows="4"
                          class="w-full border border-gray-300 rounded px-3 py-2"
                          placeholder="{{ __('resenas.placeholder_comentario') }}"></textarea>

                <div class="text-right">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded">
                        {{ __('resenas.enviar_resena') }}
                    </button>
                </div>
            </form>
        </div>
    @else
        <p class="mt-6 text-center text-gray-500">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                {{ __('resenas.inicia_sesion') }}
            </a>
        </p>
    @endauth
</div>
@endsection