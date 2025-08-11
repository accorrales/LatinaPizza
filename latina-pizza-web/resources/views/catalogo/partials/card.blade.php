<div class="relative bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 group border border-gray-100">

    <div class="relative h-48 sm:h-52 md:h-56">
        <img src="{{ $sabor['imagen'] }}"
             alt="{{ $sabor['sabor_nombre'] }}"
             class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">

        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent z-10"></div>

        <div class="absolute bottom-4 left-4 z-20">
            <h3 class="text-white text-2xl font-bold drop-shadow-sm">{{ $sabor['sabor_nombre'] }}</h3>
            <p class="text-white text-sm">{{ $sabor['descripcion'] }}</p>
        </div>

        <div class="absolute top-3 left-3 z-20">
            <span class="bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full shadow">
                🔥 {{ __('catalogo.favorito') }}
            </span>
        </div>
    </div>

    <div class="p-4 flex flex-col gap-2">
        <div class="flex items-center gap-1 text-yellow-400 text-sm">
            @php $promedio = round($sabor['promedio'], 1); @endphp
            @for ($i = 1; $i <= 5; $i++)
                @if ($promedio >= $i)
                    <i class="fas fa-star"></i>
                @elseif ($promedio >= $i - 0.5)
                    <i class="fas fa-star-half-alt"></i>
                @else
                    <i class="far fa-star"></i>
                @endif
            @endfor
        </div>

        <a href="{{ route('sabor.resenas', $sabor['sabor_id']) }}"
           class="text-sm text-blue-600 hover:underline">
            {{ __('catalogo.ver_resenas') }}
        </a>
    </div>

    <button
        onclick="abrirModal(this)"
        data-sabor='@json($sabor)'
        class="absolute bottom-4 right-4 z-20 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full text-sm font-semibold shadow-lg transition-all duration-300"
    >
        {{ __('catalogo.ver_tamanos') }} 🍕
    </button>
</div>







