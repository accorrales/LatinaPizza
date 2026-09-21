@extends('layouts.app')

@section('meta_description', 'Revisá tu pedido, elegí el método de pago y completá tu compra en Latina Pizza.')

@section('content')
@php
  $data = $carrito['data'] ?? [];
  $items = $data['items'] ?? [];
  $subtotal = $carrito['subtotal'] ?? 0;
  $deliveryF = $carrito['delivery']['fee'] ?? 0;
  $deliveryC = $carrito['delivery']['currency'] ?? '₡';
  $distance = $carrito['delivery']['distance'] ?? null;
  $total = $carrito['total'] ?? 0;
  $stripeEnabled = filled(config('services.stripe.key'));
@endphp

<div class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 bg-[#f7f9fc] text-slate-950 min-h-[70vh]">
  <div class="mx-auto max-w-[1296px] px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-14">
    <header class="mb-8 sm:mb-10">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.15em] text-blue-700">
            <i class="fas fa-shopping-bag"></i>
            Tu pedido
          </span>
          <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-bold tracking-[-0.04em] text-[#071426]">
            {{ __('carrito.mi_carrito') }}
          </h1>
          <p class="mt-3 max-w-2xl text-sm sm:text-base leading-7 text-slate-500">
            Revisá los detalles, elegí cómo pagar y confirmá. Nosotros nos encargamos del resto.
          </p>
        </div>

        <div class="flex items-center gap-2 text-xs sm:text-sm font-semibold text-slate-400" aria-label="Progreso del pedido">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">1</span>
          <span class="text-blue-700">Carrito</span>
          <span class="h-px w-8 bg-slate-200"></span>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white">2</span>
          <span>Pago</span>
          <span class="h-px w-8 bg-slate-200"></span>
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white">3</span>
          <span>Listo</span>
        </div>
      </div>
    </header>

    @if(session('success'))
      <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
        <i class="fas fa-circle-check mt-0.5"></i>
        <span>{{ session('success') }}</span>
      </div>
    @endif

    @if(session('error'))
      <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
        <i class="fas fa-circle-exclamation mt-0.5"></i>
        <span>{{ session('error') }}</span>
      </div>
    @endif

    @if(!empty($items))
      <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_410px] lg:items-start">
        <section class="space-y-4" aria-label="Productos del carrito">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#071426]">Lo que vas a disfrutar</h2>
              <p class="mt-1 text-sm text-slate-500">{{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }} en tu orden.</p>
            </div>
            <a href="{{ url('/catalogo') }}" class="hidden sm:inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700">
              <i class="fas fa-plus text-xs"></i>
              {{ __('carrito.seguir_comprando') }}
            </a>
          </div>

          @foreach($items as $it)
            <article class="group overflow-hidden rounded-[26px] border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(7,20,38,0.06)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_50px_rgba(7,20,38,0.10)]">
              <div class="p-5 sm:p-6">
                <div class="flex gap-4 sm:gap-5">
                  <div class="flex h-16 w-16 sm:h-20 sm:w-20 shrink-0 items-center justify-center rounded-[20px] {{ $it['tipo']==='producto' ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-600' }}">
                    <i class="fas {{ $it['tipo']==='producto' ? 'fa-pizza-slice' : 'fa-gift' }} text-2xl sm:text-3xl"></i>
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                      <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                          <h3 class="text-lg sm:text-xl font-bold text-[#071426]">{{ $it['nombre'] }}</h3>
                          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                            {{ $it['tipo']==='producto' ? 'Pizza' : 'Combo' }}
                          </span>
                        </div>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                          {{ __('carrito.cantidad') }}: {{ $it['cantidad'] ?? 1 }}
                        </p>
                      </div>
                      <div class="text-left sm:text-right">
                        <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Precio</p>
                        <p class="mt-0.5 text-xl font-bold text-red-600">{{ $deliveryC }}{{ number_format($it['precio_total'], 2) }}</p>
                      </div>
                    </div>

                    @if($it['tipo']==='producto')
                      <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">{{ __('carrito.tamano') }}: {{ $it['tamano'] ?? '-' }}</span>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">{{ __('carrito.sabor') }}: {{ $it['sabor'] ?? '-' }}</span>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5">{{ __('carrito.masa') }}: {{ $it['masa_nombre'] ?? '-' }}</span>
                      </div>

                      @if(!empty($it['extras']))
                        <div class="mt-4">
                          <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('carrito.extras') }}</p>
                          <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($it['extras'] as $ex)
                              <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">+ {{ $ex['nombre'] }}</span>
                            @endforeach
                          </div>
                        </div>
                      @endif

                      @if(!empty($it['nota_cliente']))
                        <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-sm text-amber-900">
                          <span class="font-semibold">{{ __('carrito.nota_cliente') }}:</span>
                          <span class="italic">“{{ $it['nota_cliente'] }}”</span>
                        </div>
                      @endif
                    @else
                      @if(!empty($it['descripcion']))
                        <p class="mt-3 text-sm leading-6 text-slate-500">{{ $it['descripcion'] }}</p>
                      @endif

                      <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach($it['pizzas'] as $pz)
                          @if($pz['tipo']==='pizza')
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-700">
                              <div class="flex items-center gap-2 font-semibold text-[#071426]">
                                <i class="fas fa-pizza-slice text-red-500"></i>
                                {{ $pz['sabor']['nombre'] }}
                              </div>
                              <p class="mt-1 text-xs text-slate-500">{{ $pz['masa']['nombre'] }}</p>
                              @if(!empty($pz['nota_cliente']))
                                <p class="mt-2 text-xs italic text-slate-500">“{{ $pz['nota_cliente'] }}”</p>
                              @endif
                            </div>
                          @elseif($pz['tipo']==='bebida')
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-3.5 py-3 text-sm font-semibold text-blue-700">
                              <i class="fas fa-bottle-water mr-2"></i>{{ __('carrito.bebida') }}: {{ $pz['producto']['nombre'] }}
                            </div>
                          @endif
                        @endforeach
                      </div>

                      <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                        <span>{{ __('carrito.base') }}: <strong class="text-slate-700">{{ $deliveryC }}{{ number_format($it['desglose']['base'] ?? 0, 2) }}</strong></span>
                        <span>{{ __('carrito.extras') }}: <strong class="text-slate-700">{{ $deliveryC }}{{ number_format($it['desglose']['extras'] ?? 0, 2) }}</strong></span>
                      </div>
                    @endif

                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                      <span class="text-xs text-slate-400">Preparado especialmente para vos</span>
                      <form method="POST" action="{{ route('carrito.eliminar', ['id'=>$it['id']]) }}" data-confirm="¿Quitar este producto del carrito?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-semibold text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                          <i class="far fa-trash-can"></i>
                          {{ __('carrito.eliminar') }}
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          @endforeach

          <a href="{{ url('/catalogo') }}" class="sm:hidden inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-blue-700 shadow-sm">
            <i class="fas fa-plus text-xs"></i>
            {{ __('carrito.seguir_comprando') }}
          </a>
        </section>

        <aside class="lg:sticky lg:top-28 space-y-4">
          <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(7,20,38,0.10)]">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-600">Resumen</p>
                  <h2 class="mt-1 text-xl font-bold text-[#071426]">Tu orden</h2>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                  <i class="fas fa-receipt"></i>
                </span>
              </div>
            </div>

            <div class="space-y-3 px-5 py-5 sm:px-6 text-sm">
              <div class="flex items-center justify-between text-slate-500">
                <span>Subtotal</span>
                <span class="font-semibold text-slate-800">{{ $deliveryC }}{{ number_format($subtotal,2) }}</span>
              </div>
              <div class="flex items-center justify-between gap-4 text-slate-500">
                <span>
                  Delivery
                  @if($distance)
                    <span class="ml-1 text-xs text-slate-400">({{ number_format($distance,1) }} km)</span>
                  @endif
                </span>
                <span class="font-semibold text-slate-800">{{ $deliveryC }}{{ number_format($deliveryF,2) }}</span>
              </div>
              <div class="my-4 border-t border-dashed border-slate-200"></div>
              <div class="flex items-end justify-between gap-4">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Total</p>
                  <p class="mt-1 text-xs text-slate-400">Impuestos incluidos cuando apliquen</p>
                </div>
                <span class="text-2xl sm:text-3xl font-bold tracking-tight text-red-600">{{ $deliveryC }}{{ number_format($total,2) }}</span>
              </div>
            </div>

            <form id="checkout-form"
                  method="POST"
                  action="{{ route('carrito.checkout') }}"
                  data-stripe-enabled="{{ $stripeEnabled ? '1' : '0' }}"
                  data-stripe-key="{{ config('services.stripe.key') }}"
                  data-intent-url="{{ route('carrito.stripe.intent') }}"
                  data-submit-label="{{ __('carrito.confirmar_pedido') }}"
                  class="border-t border-slate-100 px-5 py-5 sm:px-6">
              @csrf

              <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                  <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-600">Pago</p>
                  <h3 class="mt-1 text-lg font-bold text-[#071426]">¿Cómo querés pagar?</h3>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                  <i class="fas fa-lock text-[10px]"></i> Seguro
                </span>
              </div>

              <div class="space-y-2.5">
                <label class="block cursor-pointer">
                  <input class="peer sr-only" type="radio" name="metodo_pago" value="efectivo" checked>
                  <span class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition peer-checked:border-blue-500 peer-checked:bg-blue-50/70 peer-checked:ring-2 peer-checked:ring-blue-500/10">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-money-bill-wave"></i></span>
                    <span class="min-w-0 flex-1">
                      <span class="block text-sm font-semibold text-slate-800">Efectivo</span>
                      <span class="block text-xs text-slate-500">Pagás al recibir o retirar</span>
                    </span>
                    <i class="fas fa-circle-check text-blue-600 opacity-0 transition peer-checked:opacity-100"></i>
                  </span>
                </label>

                <label class="block cursor-pointer">
                  <input class="peer sr-only" type="radio" name="metodo_pago" value="datafono">
                  <span class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition peer-checked:border-blue-500 peer-checked:bg-blue-50/70 peer-checked:ring-2 peer-checked:ring-blue-500/10">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-credit-card"></i></span>
                    <span class="min-w-0 flex-1">
                      <span class="block text-sm font-semibold text-slate-800">Datáfono</span>
                      <span class="block text-xs text-slate-500">Tarjeta en local o con repartidor</span>
                    </span>
                  </span>
                </label>

                @if ($stripeEnabled)
                  <label class="block cursor-pointer">
                    <input class="peer sr-only" type="radio" name="metodo_pago" value="stripe">
                    <span class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition peer-checked:border-blue-500 peer-checked:bg-blue-50/70 peer-checked:ring-2 peer-checked:ring-blue-500/10">
                      <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fas fa-shield-halved"></i></span>
                      <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-slate-800">Tarjeta en línea</span>
                        <span class="block text-xs text-slate-500">Pago protegido con Stripe</span>
                      </span>
                    </span>
                  </label>
                @endif
              </div>

              <div id="stripe-box" class="hidden mt-4 rounded-2xl border border-blue-100 bg-blue-50/40 p-4">
                <div class="mb-3 flex items-center gap-2 text-xs font-semibold text-blue-700">
                  <i class="fas fa-lock"></i>
                  Tus datos de tarjeta se procesan de forma segura.
                </div>
                <div id="payment-element"></div>
                <div id="payment-error" class="hidden mt-3 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-600"></div>
              </div>

              <input type="hidden" name="payment_intent_id" id="payment_intent_id">

              <button id="btn-submit" type="submit" class="relative mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-4 text-sm font-bold text-white shadow-[0_12px_30px_rgba(220,38,38,0.25)] transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-[0_16px_36px_rgba(220,38,38,0.32)] disabled:cursor-not-allowed disabled:opacity-60">
                <svg id="btn-spinner" class="hidden h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="btn-text">{{ __('carrito.confirmar_pedido') }}</span>
                <i class="fas fa-arrow-right text-xs"></i>
              </button>

              <div class="mt-4 flex items-center justify-center gap-4 text-[11px] font-medium text-slate-400">
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-shield-halved"></i> Compra segura</span>
                <span class="inline-flex items-center gap-1.5"><i class="fas fa-bolt"></i> Confirmación rápida</span>
              </div>
            </form>
          </div>
        </aside>
      </div>
    @else
      <section class="mx-auto max-w-2xl rounded-[32px] border border-slate-200 bg-white px-6 py-14 sm:px-10 sm:py-20 text-center shadow-[0_20px_60px_rgba(7,20,38,0.08)]">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 text-blue-600">
          <i class="fas fa-cart-shopping text-3xl"></i>
        </div>
        <h2 class="mt-6 text-2xl sm:text-3xl font-bold tracking-tight text-[#071426]">{{ __('carrito.carrito_vacio') }}</h2>
        <p class="mx-auto mt-3 max-w-md text-sm sm:text-base leading-7 text-slate-500">Todavía no hay nada por acá. Elegí tu pizza favorita o armala exactamente como te gusta.</p>
        <a href="{{ url('/catalogo') }}" class="mt-7 inline-flex items-center justify-center gap-2 rounded-2xl bg-red-600 px-6 py-3.5 text-sm font-bold text-white shadow-[0_12px_30px_rgba(220,38,38,0.24)] transition hover:-translate-y-0.5 hover:bg-red-700">
          <i class="fas fa-pizza-slice"></i>
          {{ __('carrito.seguir_comprando') }}
        </a>
      </section>
    @endif
  </div>
</div>
@endsection
