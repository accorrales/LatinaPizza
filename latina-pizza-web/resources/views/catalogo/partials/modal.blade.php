<div id="modalSabor" class="fixed inset-0 z-50 hidden bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white text-gray-800 rounded-3xl w-full max-w-2xl relative shadow-2xl animate-fade-in-down overflow-hidden border border-red-600 max-h-[95vh] flex flex-col">

    <!-- Contenido scrollable -->
    <div class="overflow-y-auto p-6 sm:p-8 flex-1 relative">

      <!-- ❌ Botón cerrar -->
      <button onclick="cerrarModal()" aria-label="{{ __('catalogo.cerrar') }}"
        class="sticky top-0 left-full transform -translate-x-12 z-20 bg-white text-red-600 border border-red-300 rounded-full w-9 h-9 flex items-center justify-center shadow hover:bg-red-100 transition">
        <i class="fas fa-times text-xl"></i>
      </button>

      <!-- Imagen -->
      <img id="modalImagen" class="w-full h-52 object-cover rounded-xl mb-4 shadow" alt="{{ __('catalogo.imagen_producto_alt') }}">

      <!-- Nombre + descripción -->
      <h2 id="modalNombre" class="text-3xl font-extrabold text-red-600 mb-1 text-center"></h2>
      <p id="modalDescripcion" class="text-center text-gray-600 text-sm mb-5"></p>

      <form id="formAgregarProducto">
        @csrf
        <input type="hidden" name="producto_id" id="inputProductoId">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

          <!-- Tamaños -->
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1 block">{{ __('catalogo.tamano') }}</label>
            <div id="modalTamanos" class="flex flex-wrap gap-2"></div>
          </div>

          <!-- Masas -->
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1 block">{{ __('catalogo.tipo_masa') }}</label>
            <select name="masa_id" id="masa" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-red-400">
            </select>
          </div>

          <!-- Extras -->
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700 mb-1 block">{{ __('catalogo.extras') }}</label>
            <div id="extrasOpciones" class="grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
          </div>
        </div>

        <!-- Precio -->
        <div class="text-right mt-4 text-lg font-semibold text-green-600">
          {{ __('catalogo.total') }} <span id="precioTotal">₡0.00</span>
          <input type="hidden" name="precio_total" id="inputPrecioTotal">
        </div>

        <!-- Nota personalizada -->
        <div class="mt-4">
          <label for="nota" class="block text-sm font-medium text-gray-700 mb-1">📝 {{ __('catalogo.nota_personalizada') }}</label>
          <textarea name="nota_cliente" id="nota" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-700 shadow-sm" placeholder="{{ __('catalogo.nota_placeholder') }}"></textarea>
        </div>

        <!-- Botón agregar -->
        <button type="submit" class="mt-6 w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold text-lg shadow transition">
          🛒 {{ __('catalogo.agregar_carrito') }}
        </button>
      </form>
    </div>
  </div>
</div>

<div id="modalConfirmacion" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden flex items-center justify-center px-4 animate-fade-in-down">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
    <h3 class="text-xl font-semibold text-green-700 mb-4">🎉 {{ __('catalogo.producto_agregado') }}</h3>
    <p class="text-gray-700 mb-6">{{ __('catalogo.que_deseas_ahora') }}</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <button onclick="cerrarModalConfirmacion()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-xl w-full">
        🔁 {{ __('catalogo.seguir_comprando') }}
      </button>
      <a href="/carrito" id="btnIrAlCarrito" onclick="mostrarLoading()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-xl w-full text-center">
        🛒 {{ __('catalogo.ir_carrito') }}
      </a>
    </div>
  </div>
</div>


