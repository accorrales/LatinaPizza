<div class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-4 flex flex-col justify-between h-full">

    <!-- Imagen -->
    <img src="{{ $sabor['imagen'] }}"
         alt="{{ $sabor['sabor_nombre'] }}"
         class="w-full h-40 object-cover rounded-xl mb-3 border border-gray-200">

    <!-- Título y descripción -->
    <h3 class="text-lg font-bold text-red-600 mb-1">{{ $sabor['sabor_nombre'] }}</h3>
    <p class="text-sm text-gray-600 flex-grow">{{ $sabor['descripcion'] }}</p>

    <!-- Estrellas -->
    <div class="flex items-center mt-2 mb-2">
        @php
            $promedio = round($sabor['promedio'], 1);
        @endphp

        @for ($i = 1; $i <= 5; $i++)
            @if ($promedio >= $i)
                <i class="fas fa-star text-yellow-400"></i>
            @elseif ($promedio >= $i - 0.5)
                <i class="fas fa-star-half-alt text-yellow-400"></i>
            @else
                <i class="far fa-star text-yellow-400"></i>
            @endif
        @endfor

        <span class="ml-2 text-sm text-gray-700">
            ({{ number_format($promedio, 1) }} / 5 - {{ $sabor['total_resenas'] }} reseñas)
        </span>
    </div>

    <!-- Link -->
    <a href="{{ route('sabor.resenas', $sabor['sabor_id']) }}"
       class="text-sm text-blue-500 hover:underline mb-3">
       Ver reseñas
    </a>

    <!-- Botón -->
    <button
        onclick="abrirModal(this)"
        data-sabor='@json($sabor)'
        class="mt-auto w-full bg-gradient-to-r from-red-600 to-red-500 text-white px-4 py-2 rounded-full hover:from-red-700 hover:to-red-600 text-sm font-semibold shadow"
    >
        Ver tamaños y precios
    </button>

</div>





