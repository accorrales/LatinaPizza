<div id="modalPromocion" class="fixed inset-0 z-50 hidden bg-black bg-opacity-70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl w-full max-w-3xl relative shadow-2xl animate-fade-in-down overflow-hidden border border-red-500 max-h-[95vh] flex flex-col">
    <div class="sticky top-0 bg-white z-20 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
      <h2 class="text-xl sm:text-2xl font-bold text-red-600 flex items-center gap-2">
        🎁 <span>{{ __('catalogo.personalizar_promocion') }}</span>
      </h2>
      <button type="button" data-close-promotion-modal aria-label="{{ __('catalogo.cerrar') }}"
              class="bg-white/80 hover:bg-white text-red-600 hover:text-red-700 border border-red-200 rounded-full w-9 h-9 flex items-center justify-center shadow transition-all duration-200">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>

    <div id="contenedorPizzaPersonalizada" class="overflow-y-auto p-6 sm:p-8 flex-1 space-y-6 text-sm text-gray-800">
      <p class="text-center text-gray-500">{{ __('catalogo.cargando_promocion') }}</p>
    </div>

    <div class="sticky bottom-0 z-10 bg-white border-t border-gray-200 px-6 sm:px-8 py-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-green-700 text-xl font-semibold text-center sm:text-left" id="totalPromo">
          {{ __('catalogo.total') }} ₡0.00
        </div>
        <button type="button" data-add-promotion
                class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md transition duration-300">
          🛒 {{ __('catalogo.agregar_carrito') }}
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  input[type="checkbox"], select, textarea {
    border-radius: .5rem;
    padding: .5rem;
    border: 1px solid #d1d5db;
    transition: all .2s;
  }
  input[type="checkbox"]:hover, select:hover, textarea:hover { border-color: #9ca3af; }
  select:focus, textarea:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, .2);
  }
  #modalPromocion::-webkit-scrollbar { width: 8px; }
  #modalPromocion::-webkit-scrollbar-thumb { background: #ef4444; border-radius: 4px; }
</style>
