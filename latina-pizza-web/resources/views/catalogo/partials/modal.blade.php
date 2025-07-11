<!-- Modal Estilizado de Pedido Personalizado -->
<div id="modalSabor" class="fixed inset-0 z-50 hidden bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl relative shadow-2xl animate-fade-in-down overflow-hidden border border-red-500">
        <!-- Botón cerrar -->
        <button onclick="cerrarModal()" class="absolute top-3 right-3 text-red-500 text-3xl font-bold hover:text-red-700 transition">
            &times;
        </button>

        <form method="POST" action="{{ route('carrito.agregar') }}" class="p-6 sm:p-8">
            @csrf

            <!-- Imagen destacada -->
            <img id="modalImagen" class="w-full h-52 object-cover rounded-xl mb-4 shadow-sm border border-gray-200" alt="">

            <!-- Nombre del producto -->
            <h2 id="modalNombre" class="text-3xl font-bold text-red-600 mb-2 text-center"></h2>
            <p id="modalDescripcion" class="text-gray-600 text-sm mb-4 text-center"></p>

            <input type="hidden" name="producto_id" id="inputProductoId">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Tamaño -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Tamaño</label>
                    <div id="modalTamanos" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- Masa -->
                <div>
                    <label for="masa" class="block text-sm font-semibold text-gray-800 mb-1">Tipo de masa</label>
                    <select name="masa_id" id="masa" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    </select>
                </div>

                <!-- Extras -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Extras</label>
                    <div id="extrasOpciones" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
                </div>
            </div>

            <!-- Total dinámico -->
            <div class="text-right mt-4 text-lg font-semibold text-green-600">
                Total: <span id="precioTotal">₡0.00</span>
                <input type="hidden" name="precio_total" id="inputPrecioTotal">
            </div>

            <!-- Nota personalizada -->
            <div class="mt-4">
                <label for="nota" class="block text-sm font-semibold text-gray-800 mb-1">📝 Nota personalizada</label>
                <textarea name="nota_cliente" id="nota" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-700 shadow-sm" placeholder="Ej: sin cebolla, extra queso..."></textarea>
            </div>

            <!-- Botón de agregar -->
            <button type="submit" class="mt-6 w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold text-lg transition-all duration-200 shadow-md">
                Agregar al carrito
            </button>
        </form>
    </div>
</div>

