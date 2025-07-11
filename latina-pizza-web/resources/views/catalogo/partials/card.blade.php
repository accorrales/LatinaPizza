<div class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-4 flex flex-col justify-between h-full">
    <img src="{{ $sabor['imagen'] }}"
         alt="{{ $sabor['sabor_nombre'] }}"
         class="w-full h-40 object-cover rounded-xl mb-3 border border-gray-200">

    <h3 class="text-xl font-bold text-red-600 mb-1">{{ $sabor['sabor_nombre'] }}</h3>
    <p class="text-gray-600 text-sm flex-grow">{{ $sabor['descripcion'] }}</p>

    <div class="mt-4">
        <button
            onclick="abrirModal(this)"
            data-sabor='@json($sabor)'
            class="w-full bg-gradient-to-r from-red-600 to-red-500 text-white px-4 py-2 rounded-full hover:from-red-700 hover:to-red-600 text-sm font-semibold shadow"
        >
            Ver tamaños y precios
        </button>
    </div>
</div>



