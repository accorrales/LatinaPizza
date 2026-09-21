<div id="modalSabor" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-[#071426]/75 p-3 backdrop-blur-md sm:p-5">
  <div class="relative flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-[0_35px_100px_rgba(7,20,38,0.45)] lg:flex-row">
    <button
      type="button"
      data-close-product-modal
      aria-label="{{ __('catalogo.cerrar') }}"
      class="absolute right-4 top-4 z-30 grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:bg-slate-100 hover:text-red-500"
    >
      <i class="fas fa-times"></i>
    </button>

    <div class="relative h-52 shrink-0 overflow-hidden bg-[#071426] sm:h-64 lg:h-auto lg:w-[42%]">
      <img id="modalImagen" class="h-full w-full object-cover" alt="{{ __('catalogo.imagen_producto_alt') }}">
      <div class="absolute inset-0 bg-gradient-to-t from-[#071426]/85 via-transparent to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-[#071426]/20"></div>
      <div class="absolute bottom-5 left-5 right-5 lg:bottom-8 lg:left-8">
        <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-white backdrop-blur">Pizza Builder</span>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
      <div class="pr-10">
        <h2 id="modalNombre" class="text-3xl font-bold tracking-[-0.035em] text-slate-950 sm:text-4xl"></h2>
        <p id="modalDescripcion" class="mt-3 max-w-xl text-sm leading-6 text-slate-500"></p>
      </div>

      <form id="formAgregarProducto" class="mt-8">
        @csrf
        <input type="hidden" name="producto_id" id="inputProductoId">

        <div class="space-y-7">
          <fieldset>
            <div class="mb-3 flex items-center gap-3">
              <span class="grid h-7 w-7 place-items-center rounded-full bg-red-500 text-[11px] font-bold text-white">1</span>
              <legend class="text-sm font-bold text-slate-900">{{ __('catalogo.tamano') }}</legend>
            </div>
            <div id="modalTamanos" class="flex flex-wrap gap-2.5"></div>
          </fieldset>

          <div>
            <div class="mb-3 flex items-center gap-3">
              <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 bg-white text-[11px] font-bold text-blue-500">2</span>
              <label for="masa" class="text-sm font-bold text-slate-900">{{ __('catalogo.tipo_masa') }}</label>
            </div>
            <select name="masa_id" id="masa" class="w-full rounded-2xl border border-slate-200 bg-[#F8FAFC] px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"></select>
          </div>

          <fieldset>
            <div class="mb-3 flex items-center gap-3">
              <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 bg-white text-[11px] font-bold text-blue-500">3</span>
              <legend class="text-sm font-bold text-slate-900">{{ __('catalogo.extras') }}</legend>
            </div>
            <div id="extrasOpciones" class="grid grid-cols-1 gap-2.5 sm:grid-cols-2"></div>
          </fieldset>

          <div>
            <div class="mb-3 flex items-center gap-3">
              <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-200 bg-white text-[11px] font-bold text-blue-500">4</span>
              <label for="nota" class="text-sm font-bold text-slate-900">{{ __('catalogo.nota_personalizada') }}</label>
            </div>
            <textarea name="nota_cliente" id="nota" rows="3" class="w-full resize-none rounded-2xl border border-slate-200 bg-[#F8FAFC] px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" placeholder="{{ __('catalogo.nota_placeholder') }}"></textarea>
          </div>
        </div>

        <div class="mt-8 flex flex-col gap-4 rounded-[22px] bg-[#F6F9FF] p-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">{{ __('catalogo.total') }}</p>
            <p id="precioTotal" class="mt-1 text-3xl font-bold tracking-[-0.03em] text-red-500">₡0.00</p>
            <input type="hidden" name="precio_total" id="inputPrecioTotal">
          </div>
          <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-950/10 transition hover:-translate-y-0.5 hover:bg-red-600 active:scale-[0.98]">
            <i class="fas fa-cart-plus"></i>
            {{ __('catalogo.agregar_carrito') }}
          </button>
        </div>
      </form>
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
