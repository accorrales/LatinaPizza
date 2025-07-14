<div id="modalPromocion" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 hidden px-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 relative">

    <!-- Botón cerrar -->
    <button onclick="cerrarModalPromocion()" class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl font-bold">
      &times;
    </button>

    <!-- Título -->
    <h2 class="text-2xl font-bold text-red-600 mb-6 text-center flex items-center justify-center gap-2">
      🎁 <span>Personalizar Promoción</span>
    </h2>

    <!-- Contenido dinámico -->
    <div id="contenedorPizzaPersonalizada" class="space-y-6 text-sm text-gray-800">
      <p class="text-center text-gray-500 text-sm">Cargando componentes de la promoción...</p>
    </div>

    <!-- Total -->
    <div class="mt-8 text-center text-green-700 text-xl font-semibold" id="totalPromo">
      Total: ₡0.00
    </div>

    <!-- Botón agregar al carrito -->
    <div class="sticky bottom-0 bg-white py-4 text-center mt-6 border-t border-gray-200">
      <button onclick="agregarPromocionAlCarrito()"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md transition duration-300 w-full sm:w-auto">
        🛒 Agregar al Carrito
      </button>
    </div>

  </div>
</div>

<!-- Modal Confirmación -->
<div id="modalConfirmacion" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden flex items-center justify-center px-4 transition duration-300 ease-in-out">
  <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center relative animate-fade-in">
    
    <!-- Ícono -->
    <div class="text-green-600 text-5xl mb-4">🎉</div>

    <!-- Título -->
    <h3 class="text-2xl font-bold text-gray-800 mb-2">¡Promoción agregada!</h3>

    <!-- Descripción -->
    <p class="text-gray-600 mb-6">¿Qué deseas hacer ahora?</p>

    <!-- Botones -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <button onclick="cerrarModalConfirmacion()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-xl w-full transition">
        🔁 Seguir comprando
      </button>
      <a href="/carrito" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-xl w-full text-center transition">
        🛒 Ir al carrito
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






