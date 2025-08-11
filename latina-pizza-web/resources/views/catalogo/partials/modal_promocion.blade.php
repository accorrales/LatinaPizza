<!-- ✅ Modal Personalización Promoción -->
<div id="modalPromocion" class="fixed inset-0 z-50 hidden bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl w-full max-w-3xl relative shadow-2xl animate-fade-in-down overflow-hidden border border-red-500
              max-h-[95vh] flex flex-col">

    <!-- ✅ Header sticky -->
    <div class="sticky top-0 bg-white z-20 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
      <h2 class="text-xl sm:text-2xl font-bold text-red-600 flex items-center gap-2">
        🎁 <span>{{ __('catalogo.personalizar_promocion') }}</span>
      </h2>
      <button onclick="cerrarModalPromocion()" aria-label="{{ __('catalogo.cerrar') }}"
              class="bg-white/80 hover:bg-white text-red-600 hover:text-red-700 border border-red-200 rounded-full w-9 h-9 flex items-center justify-center shadow transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>

    <!-- ✅ Contenido dinámico -->
    <div id="contenedorPizzaPersonalizada" class="overflow-y-auto p-6 sm:p-8 flex-1 space-y-6 text-sm text-gray-800">
      <p class="text-center text-gray-500">{{ __('catalogo.cargando_promocion') }}</p>
    </div>

    <!-- ✅ Total y botón sticky abajo -->
    <div class="sticky bottom-0 z-10 bg-white border-t border-gray-200 px-6 sm:px-8 py-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-green-700 text-xl font-semibold text-center sm:text-left" id="totalPromo">
          {{ __('catalogo.total') }} ₡0.00
        </div>
        <button onclick="agregarPromocionAlCarrito()"
                class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md transition duration-300">
          🛒 {{ __('catalogo.agregar_carrito') }}
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Confirmación -->
<div id="modalConfirmacion" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden flex items-center justify-center px-4 transition duration-300 ease-in-out">
  <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center relative animate-fade-in">
    
    <!-- Ícono -->
    <div class="text-green-600 text-5xl mb-4">🎉</div>

    <!-- Título -->
    <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ __('catalogo.promocion_agregada') }}</h3>

    <!-- Descripción -->
    <p class="text-gray-600 mb-6">{{ __('catalogo.que_deseas_ahora') }}</p>

    <!-- Botones -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <button onclick="cerrarModalConfirmacion()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-xl w-full transition">
        🔁 {{ __('catalogo.seguir_comprando') }}
      </button>
      <a href="/carrito" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-xl w-full text-center transition">
        🛒 {{ __('catalogo.ir_carrito') }}
      </a>
    </div>

    <!-- Botón Cerrar arriba -->
    <button onclick="cerrarModalConfirmacion()" class="absolute top-3 right-4 text-gray-400 hover:text-red-500 text-xl font-bold">
      &times;
    </button>
  </div>
</div>

<!-- Estilos -->
<style>
  input[type="checkbox"],
  select,
  textarea {
    border-radius: 0.5rem;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    transition: all 0.2s;
  }

  input[type="checkbox"]:hover,
  select:hover,
  textarea:hover {
    border-color: #9ca3af;
  }

  select:focus,
  textarea:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
  }

  /* Estética scroll */
  #modalPromocion::-webkit-scrollbar {
    width: 8px;
  }
  #modalPromocion::-webkit-scrollbar-thumb {
    background: #ef4444;
    border-radius: 4px;
  }
</style>






