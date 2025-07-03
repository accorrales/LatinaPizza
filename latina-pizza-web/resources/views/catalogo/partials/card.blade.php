<div class="bg-white rounded shadow p-4 relative h-full flex flex-col justify-between">
    <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="w-full h-40 object-cover rounded mb-2">
    <h3 class="text-lg font-semibold">{{ $producto['nombre'] }}</h3>
    <p class="text-red-600 font-bold mt-1">₡{{ number_format($producto['precio'], 2) }}</p>

    <div class="flex justify-between mt-3">
        <button
            onclick='mostrarDetalle(@json($producto))'
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
        >
            Ver más
        </button>
        <form action="{{ route('carrito.agregar') }}" method="POST">
            @csrf
            <input type="hidden" name="producto_id" value="{{ $producto['id'] }}">
            <input type="hidden" name="cantidad" value="1">
            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                Agregar
            </button>
        </form>
    </div>
</div>

