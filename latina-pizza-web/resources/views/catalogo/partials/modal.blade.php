<div id="modalSabor" class="pizza-builder fixed inset-0 z-50 hidden flex items-center justify-center bg-[#071426]/75 p-3 backdrop-blur-md sm:p-5">
  <div class="relative flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-[0_35px_100px_rgba(7,20,38,0.45)] lg:flex-row">
    <button
      type="button"
      data-close-product-modal
      aria-label="{{ __('catalogo.cerrar') }}"
      class="absolute right-4 top-4 z-30 grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:bg-red-500 hover:text-white hover:border-red-500"
    >
      <i class="fas fa-times"></i>
    </button>

    {{-- LADO IZQUIERDO: imagen apetitosa de la pizza --}}
    <div class="pizza-builder__stage relative h-64 shrink-0 overflow-hidden bg-[#071426] sm:h-80 lg:h-auto lg:w-[45%]">
      <div class="pizza-builder__glow pizza-builder__glow--red"></div>
      <div class="pizza-builder__glow pizza-builder__glow--blue"></div>
      <img id="modalImagen" class="pizza-builder__pizza relative z-10 h-full w-full object-contain p-6 sm:p-10 drop-shadow-[0_30px_50px_rgba(0,0,0,0.45)]" alt="{{ __('catalogo.imagen_producto_alt') }}">

      <div class="absolute left-5 top-5 z-20 flex flex-col gap-2">
        <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-white backdrop-blur">
          <i class="fas fa-pizza-slice text-red-400"></i> Arma tu pizza
        </span>
        <span id="modalRating" class="hidden w-fit items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-bold text-slate-800 shadow-lg"></span>
      </div>
    </div>

    {{-- LADO DERECHO: configurador --}}
    <div class="flex flex-1 flex-col overflow-hidden">
      <div class="flex-1 overflow-y-auto px-5 py-6 sm:px-8 sm:py-7 lg:px-9">
        <div class="pr-10">
          <h2 id="modalNombre" class="text-2xl font-bold tracking-[-0.035em] text-slate-950 sm:text-3xl"></h2>
          <p id="modalDescripcion" class="mt-2 max-w-xl text-sm leading-6 text-slate-500"></p>
        </div>

        <form id="formAgregarProducto" class="mt-6">
          @csrf
          <input type="hidden" name="producto_id" id="inputProductoId">

          <div class="space-y-6">
            {{-- Tamaño --}}
            <fieldset>
              <div class="mb-3 flex items-center justify-between">
                <legend class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('catalogo.tamano') }}</legend>
              </div>
              <div id="modalTamanos" class="grid grid-cols-4 gap-2.5"></div>
            </fieldset>

            {{-- Masa (estilo salsa) --}}
            <fieldset>
              <div class="mb-3 flex items-center justify-between">
                <legend class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('catalogo.tipo_masa') }}</legend>
              </div>
              <div id="masaOpciones" class="flex flex-wrap gap-2.5"></div>
            </fieldset>

            {{-- Toppings / Extras --}}
            <fieldset>
              <div class="mb-3 flex items-center justify-between">
                <legend class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('catalogo.extras') }}</legend>
                <span class="text-[11px] font-medium text-slate-400">Opcional</span>
              </div>
              <div id="extrasOpciones" class="space-y-2"></div>
            </fieldset>

            {{-- Nota --}}
            <details class="group rounded-2xl border border-slate-200 bg-[#F8FAFC] open:bg-white">
              <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700">
                <span class="flex items-center gap-2"><i class="fas fa-pen text-blue-500"></i> {{ __('catalogo.nota_personalizada') }}</span>
                <i class="fas fa-chevron-down text-slate-400 transition group-open:rotate-180"></i>
              </summary>
              <div class="px-4 pb-4">
                <textarea name="nota_cliente" id="nota" rows="2" class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="{{ __('catalogo.nota_placeholder') }}"></textarea>
              </div>
            </details>
          </div>
        </form>
      </div>

      {{-- Footer fijo: total + ETA + agregar --}}
      <div class="border-t border-slate-100 bg-white/95 px-5 py-4 backdrop-blur sm:px-8 lg:px-9">
        <div class="flex items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">{{ __('catalogo.total') }}</p>
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600"><i class="fas fa-clock"></i> 25-35 min</span>
            </div>
            <p id="precioTotal" class="mt-0.5 text-[28px] font-bold leading-none tracking-[-0.03em] text-red-500">₡0.00</p>
            <input type="hidden" name="precio_total" id="inputPrecioTotal">
          </div>
          <button type="submit" form="formAgregarProducto" class="pizza-builder__cta home-shine inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/15 transition hover:-translate-y-0.5 hover:bg-red-600 active:scale-[0.98]">
            <i class="fas fa-cart-plus"></i>
            {{ __('catalogo.agregar_carrito') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="modalConfirmacion" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-[#071426]/70 px-4 backdrop-blur-sm">
  <div class="w-full max-w-md rounded-[28px] border border-white/20 bg-white p-7 text-center shadow-[0_30px_80px_rgba(7,20,38,0.4)] sm:p-8">
    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-50 text-2xl text-emerald-600"><i class="fas fa-check"></i></span>
    <h3 class="mt-5 text-2xl font-bold tracking-[-0.025em] text-slate-950">{{ __('catalogo.producto_agregado') }}</h3>
    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('catalogo.que_deseas_ahora') }}</p>
    <div class="mt-7 grid gap-3 sm:grid-cols-2">
      <button type="button" data-close-confirmation-modal class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ __('catalogo.seguir_comprando') }}
      </button>
      <a href="/carrito" id="btnIrAlCarrito" data-show-loading class="rounded-full bg-red-500 px-5 py-3 text-center text-sm font-semibold text-white shadow-lg transition hover:bg-red-600">
        {{ __('catalogo.ir_carrito') }}
      </a>
    </div>
  </div>
</div>
