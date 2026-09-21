<div id="modalPromocion" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-[#071426]/75 p-3 backdrop-blur-md sm:p-5">
  <div class="flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-[0_35px_100px_rgba(7,20,38,0.45)]">
    <div class="flex items-center justify-between border-b border-slate-100 bg-white px-5 py-5 sm:px-8">
      <div>
        <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-500">Combo Builder</span>
        <h2 class="mt-1 text-xl font-bold tracking-[-0.025em] text-slate-950 sm:text-2xl">{{ __('catalogo.personalizar_promocion') }}</h2>
      </div>
      <button
        type="button"
        data-close-promotion-modal
        aria-label="{{ __('catalogo.cerrar') }}"
        class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100 hover:text-red-500"
      >
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div id="contenedorPizzaPersonalizada" class="promotion-builder flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-5 text-sm text-slate-800 sm:p-8">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
        <span class="mx-auto mb-3 block h-8 w-8 animate-spin rounded-full border-2 border-blue-100 border-t-blue-500"></span>
        {{ __('catalogo.cargando_promocion') }}
      </div>
    </div>

    <div class="border-t border-slate-100 bg-white px-5 py-5 sm:px-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">{{ __('catalogo.total') }}</p>
          <div class="mt-1 text-2xl font-bold tracking-[-0.025em] text-red-500 sm:text-3xl" id="totalPromo">₡0.00</div>
        </div>
        <button
          type="button"
          data-add-promotion
          class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-950/10 transition hover:-translate-y-0.5 hover:bg-red-600 active:scale-[0.98]"
        >
          <i class="fas fa-cart-plus"></i>
          {{ __('catalogo.agregar_carrito') }}
        </button>
      </div>
    </div>
  </div>
</div>
