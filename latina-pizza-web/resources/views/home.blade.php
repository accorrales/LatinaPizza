@extends('layouts.app')

@section('meta_description', 'Ordená pizzas, promociones y entrega express con Latina Pizza. Personalizá tu pizza y elegí Pickup o Express.')

@section('content')
@php
    $homeSabores = collect($sabores ?? [])->take(8);
    $homePromociones = collect($promociones ?? [])->take(8);
@endphp

<div
    class="home-storefront w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 bg-white text-slate-950 overflow-hidden"
    data-home-page
    data-catalog-root
    data-api-url="{{ config('app.api_url') }}"
    data-login-url="{{ route('login') }}"
    data-i18n='{{ json_encode(trans('catalogo'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
>
    <div class="mx-auto max-w-[1296px] px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 pb-20 sm:pb-28">
        {{-- Hero / carrusel de entrada --}}
        <section class="home-reveal mb-12 sm:mb-16" aria-label="Destacados Latina Pizza">
            <div class="swiper home-hero-swiper overflow-hidden rounded-[28px] sm:rounded-[34px] bg-[#071426] shadow-[0_24px_70px_rgba(7,20,38,0.18)]">
                <div class="swiper-wrapper">
                    <article class="swiper-slide min-h-[500px] sm:min-h-[560px] lg:min-h-[610px]">
                        <div class="relative min-h-[500px] sm:min-h-[560px] lg:min-h-[610px] overflow-hidden bg-[#071426]">
                            <div class="home-blob home-parallax absolute -right-20 -top-28 h-[430px] w-[430px] rounded-full bg-blue-600/25 blur-3xl"></div>
                            <div class="home-blob home-blob--slow home-blob--delayed home-parallax absolute -bottom-36 right-16 h-[360px] w-[360px] rounded-full bg-red-500/20 blur-3xl"></div>

                            <div class="relative z-10 grid min-h-[500px] sm:min-h-[560px] lg:min-h-[610px] grid-cols-1 lg:grid-cols-2 gap-8 px-6 py-8 sm:px-10 sm:py-12 lg:px-14 lg:py-14">
                                <div class="flex flex-col justify-center max-w-xl">
                                    <span class="mb-6 inline-flex w-fit rounded-full bg-white/10 border border-white/10 px-4 py-2 text-[11px] sm:text-xs font-semibold tracking-[0.18em] text-white uppercase">
                                        Hecha al momento
                                    </span>
                                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-[64px] leading-[1.02] font-bold tracking-[-0.04em] text-white">
                                        Tu pizza.<br><span class="home-gradient-text">A tu manera.</span>
                                    </h1>
                                    <p class="mt-6 max-w-lg text-sm sm:text-base lg:text-lg leading-7 text-slate-300">
                                        Elegí tus sabores, masa y extras. Nosotros la preparamos y la llevamos caliente, rápido y exactamente como la querés.
                                    </p>
                                    <div class="mt-8 flex flex-wrap gap-3">
                                        <a href="{{ route('catalogo.index') }}" class="home-primary-cta home-shine inline-flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-red-950/20 transition">
                                            Ordenar ahora
                                        </a>
                                        <a href="{{ route('catalogo.index') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 hover:bg-white/15 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition">
                                            Ver menú
                                        </a>
                                    </div>
                                    <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-slate-400">
                                        <span>Entrega rápida</span>
                                        <span>Pago seguro</span>
                                        <span>7 sucursales</span>
                                    </div>
                                </div>

                                <div class="relative hidden min-h-[430px] items-center justify-center lg:flex">
                                    <div class="home-parallax absolute h-[420px] w-[420px] rounded-full bg-blue-600/20"></div>
                                    <div class="home-steam pointer-events-none absolute left-1/2 top-[12%] z-20 h-16 w-40 -translate-x-1/2">
                                        <span></span><span></span><span></span>
                                    </div>
                                    <div class="home-pizza-orbit relative h-[340px] w-[340px] rounded-full bg-gradient-to-br from-yellow-300 via-amber-300 to-orange-400 shadow-[0_34px_80px_rgba(0,0,0,0.35)] ring-[14px] ring-orange-500">
                                        <div class="home-pizza-spin absolute inset-0">
                                            <div class="absolute inset-[16px] rounded-full border-[8px] border-red-500/90"></div>
                                            @foreach ([[27,25],[63,21],[72,55],[31,66],[53,72]] as $pep)
                                                <span class="absolute h-9 w-9 rounded-full bg-red-500 shadow-inner" style="left: {{ $pep[0] }}%; top: {{ $pep[1] }}%;"></span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="absolute bottom-8 right-4 rounded-[22px] bg-white px-5 py-4 shadow-2xl">
                                        <p class="text-xs font-semibold text-slate-700">Especial Latina</p>
                                        <p class="mt-1 text-2xl font-bold text-red-500">₡8.900</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    @foreach ($homePromociones as $promo)
                        <article class="swiper-slide min-h-[500px] sm:min-h-[560px] lg:min-h-[610px]">
                            <button
                                type="button"
                                data-promotion-id="{{ $promo['id'] }}"
                                class="group relative block min-h-[500px] sm:min-h-[560px] lg:min-h-[610px] w-full overflow-hidden text-left"
                            >
                                <img
                                    src="{{ $promo['imagen'] ?? asset('images/Logo.png') }}"
                                    alt="{{ $promo['nombre'] ?? 'Promoción Latina Pizza' }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                                >
                                <div class="absolute inset-0 bg-gradient-to-r from-[#071426]/95 via-[#071426]/75 to-[#071426]/15"></div>
                                <div class="relative z-10 flex min-h-[500px] sm:min-h-[560px] lg:min-h-[610px] max-w-2xl flex-col justify-center px-7 sm:px-12 lg:px-14">
                                    <span class="mb-5 inline-flex w-fit rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur">
                                        Promoción destacada
                                    </span>
                                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-[-0.04em] text-white">
                                        {{ $promo['nombre'] ?? 'Promoción Latina' }}
                                    </h2>
                                    @if (!empty($promo['descripcion']))
                                        <p class="mt-5 max-w-xl text-base sm:text-lg leading-7 text-slate-200">{{ $promo['descripcion'] }}</p>
                                    @endif
                                    <div class="mt-8 flex items-center gap-4">
                                        @if (isset($promo['precio_total']))
                                            <span class="text-2xl sm:text-3xl font-bold text-white">₡{{ number_format((float) $promo['precio_total'], 0) }}</span>
                                        @endif
                                        <span class="inline-flex rounded-full bg-red-500 px-6 py-3 text-sm font-semibold text-white shadow-lg">
                                            Personalizar
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </article>
                    @endforeach
                </div>

                <div class="home-hero-pagination swiper-pagination !bottom-5"></div>
                <button type="button" class="home-hero-prev swiper-button-prev !hidden md:!flex !left-5 !h-11 !w-11 !rounded-full !bg-white/10 !text-white after:!text-sm backdrop-blur"></button>
                <button type="button" class="home-hero-next swiper-button-next !hidden md:!flex !right-5 !h-11 !w-11 !rounded-full !bg-white/10 !text-white after:!text-sm backdrop-blur"></button>
            </div>
        </section>

        {{-- Categorías --}}
        <section class="home-reveal mb-14 sm:mb-20">
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-[-0.025em] text-slate-950">¿Qué se te antoja hoy?</h2>
                <p class="mt-2 text-sm text-slate-500">Encontrá rápido lo que querés pedir.</p>
            </div>

            <div class="flex gap-3 overflow-x-auto pb-2 home-hide-scrollbar snap-x snap-mandatory">
                <a href="{{ route('catalogo.index') }}" data-reveal-child class="snap-start shrink-0 min-w-[150px] sm:min-w-[180px] rounded-[22px] bg-blue-500 px-5 py-5 text-white shadow-lg shadow-blue-900/10 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/20">
                    <span class="flex items-center gap-3 text-sm font-semibold">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-white/20">🍕</span>
                        Pizzas
                    </span>
                </a>
                @foreach (collect($categorias ?? [])->take(5) as $cat)
                    <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}" data-reveal-child class="snap-start shrink-0 min-w-[150px] sm:min-w-[180px] rounded-[22px] border border-slate-200 bg-[#F6F9FF] px-5 py-5 text-slate-900 transition hover:-translate-y-1 hover:border-blue-200 hover:bg-blue-50 hover:shadow-lg hover:shadow-blue-900/5">
                        <span class="flex items-center gap-3 text-sm font-semibold">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-white text-blue-500 shadow-sm">•</span>
                            {{ $cat['nombre'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Promociones --}}
        @if ($homePromociones->isNotEmpty())
            <section class="home-reveal mb-14 sm:mb-20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-[-0.025em] text-slate-950">Promos que valen la pena</h2>
                        <p class="mt-2 text-sm text-slate-500">Pensadas para compartir, ahorrar y repetir.</p>
                    </div>
                    <a href="{{ route('catalogo.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700">Ver todas →</a>
                </div>

                <div class="swiper home-promos-swiper overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach ($homePromociones as $index => $promo)
                            <article class="swiper-slide !w-[286px] sm:!w-[330px] lg:!w-[380px]">
                                <button
                                    type="button"
                                    data-promotion-id="{{ $promo['id'] }}"
                                    class="home-tilt group relative h-[330px] w-full overflow-hidden rounded-[28px] bg-[#071426] text-left shadow-sm hover:shadow-2xl"
                                >
                                    <img
                                        src="{{ $promo['imagen'] ?? asset('images/Logo.png') }}"
                                        alt="{{ $promo['nombre'] ?? 'Promoción' }}"
                                        class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#071426]/95 via-[#071426]/55 to-[#071426]/10"></div>
                                    <span class="home-tilt-glow"></span>
                                    <div class="absolute inset-x-0 bottom-0 p-6">
                                        <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-white backdrop-blur">Promo</span>
                                        <h3 class="mt-3 text-xl sm:text-2xl font-bold text-white">{{ $promo['nombre'] }}</h3>
                                        @if (isset($promo['precio_total']))
                                            <p class="mt-3 text-lg font-bold text-white">₡{{ number_format((float) $promo['precio_total'], 0) }}</p>
                                        @endif
                                    </div>
                                </button>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Productos --}}
        @if ($homeSabores->isNotEmpty())
            <section class="home-reveal mb-14 sm:mb-20">
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-[-0.025em] text-slate-950">Las más pedidas</h2>
                        <p class="mt-2 text-sm text-slate-500">Clásicos de Latina Pizza listos para personalizar.</p>
                    </div>
                    <a href="{{ route('catalogo.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700">Ver menú →</a>
                </div>

                <div class="swiper home-products-swiper overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach ($homeSabores as $sabor)
                            @php
                                $precios = collect($sabor['tamanos'] ?? [])->pluck('precio_base')->filter(fn ($precio) => is_numeric($precio));
                                $precioDesde = $precios->isNotEmpty() ? (float) $precios->min() : null;
                            @endphp
                            <article class="swiper-slide !w-[250px] sm:!w-[286px] lg:!w-[306px]">
                                <div class="home-tilt group relative min-h-[400px] overflow-hidden rounded-[26px] border border-slate-200 bg-white p-[18px] shadow-sm hover:shadow-2xl">
                                    <span class="home-tilt-glow"></span>
                                    <button
                                        type="button"
                                        data-catalog-product
                                        data-sabor='@json($sabor)'
                                        class="block w-full text-left"
                                    >
                                        <div class="relative h-[210px] overflow-hidden rounded-[20px] bg-gradient-to-br from-blue-50 to-red-50">
                                            <img
                                                src="{{ $sabor['imagen'] ?? asset('images/Logo.png') }}"
                                                alt="{{ $sabor['sabor_nombre'] }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >
                                            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-[10px] font-semibold text-slate-700 shadow-sm backdrop-blur">
                                                {{ number_format((float) ($sabor['promedio'] ?? 0), 1) }} ★
                                            </span>
                                        </div>
                                        <h3 class="mt-5 text-lg font-bold text-slate-950">{{ $sabor['sabor_nombre'] }}</h3>
                                        <p class="mt-2 line-clamp-2 min-h-[40px] text-sm leading-5 text-slate-500">{{ $sabor['descripcion'] }}</p>
                                        <div class="mt-5 flex items-center justify-between gap-3">
                                            <div>
                                                @if ($precioDesde !== null)
                                                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Desde</span>
                                                    <p class="text-lg font-bold text-red-500">₡{{ number_format($precioDesde, 0) }}</p>
                                                @else
                                                    <p class="text-sm font-semibold text-red-500">Ver tamaños</p>
                                                @endif
                                            </div>
                                            <span class="grid h-11 w-11 place-items-center rounded-full bg-blue-500 text-2xl font-medium text-white shadow-lg shadow-blue-900/15 transition group-hover:scale-105">+</span>
                                        </div>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Pizza builder --}}
        <section class="home-reveal mb-14 sm:mb-20 overflow-hidden rounded-[28px] sm:rounded-[34px] bg-[#F6F9FF] p-6 sm:p-10 lg:p-14">
            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="inline-flex rounded-full bg-blue-500 px-4 py-2 text-[10px] sm:text-xs font-semibold tracking-[0.14em] text-white uppercase">Custom Pizza Builder</span>
                    <h2 class="mt-6 text-3xl sm:text-4xl lg:text-5xl font-bold tracking-[-0.04em] text-slate-950">Armala <span class="home-gradient-text">exactamente</span><br class="hidden sm:block"> como te gusta.</h2>
                    <p class="mt-5 max-w-xl text-sm sm:text-base leading-7 text-slate-500">Escogé tamaño, masa, sabores y extras. El precio se actualiza mientras construís tu pizza.</p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach ([['Tamaño','Elegí tu tamaño'],['Masa','Tradicional o especial'],['Sabores','Una o dos mitades'],['Extras','Agregá lo que querás']] as $stepIndex => $step)
                            <div class="flex items-center gap-4">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $stepIndex === 0 ? 'bg-red-500 text-white' : 'border border-slate-200 bg-white text-blue-500' }} text-xs font-bold">{{ $stepIndex + 1 }}</span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $step[0] }}</p>
                                    <p class="text-xs text-slate-500">{{ $step[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('catalogo.index') }}" class="home-shine mt-8 inline-flex rounded-full bg-red-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-red-950/10 transition hover:bg-red-600 hover:-translate-y-0.5">
                        Empezar a crear
                    </a>
                </div>

                <div class="relative min-h-[340px] sm:min-h-[470px] overflow-hidden rounded-[26px] sm:rounded-[30px] bg-[#071426]">
                    <div class="absolute left-1/2 top-1/2 h-[250px] w-[250px] sm:h-[360px] sm:w-[360px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-blue-600/25"></div>
                    <div class="home-pizza-orbit absolute left-1/2 top-1/2 h-[210px] w-[210px] sm:h-[310px] sm:w-[310px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-br from-yellow-300 via-amber-300 to-orange-400 ring-[12px] ring-orange-500 shadow-2xl">
                        <div class="home-pizza-spin absolute inset-0">
                            <div class="absolute inset-[14px] rounded-full border-[7px] border-red-500"></div>
                            @foreach ([[28,26],[64,22],[72,57],[31,66],[55,73]] as $pep)
                                <span class="absolute h-7 w-7 sm:h-9 sm:w-9 rounded-full bg-red-500" style="left: {{ $pep[0] }}%; top: {{ $pep[1] }}%;"></span>
                            @endforeach
                        </div>
                    </div>
                    <div class="absolute bottom-5 right-5 rounded-[20px] bg-white px-5 py-4 shadow-2xl">
                        <p class="text-[10px] font-medium text-slate-500">Tu pizza</p>
                        <p class="mt-1 text-xl sm:text-2xl font-bold text-red-500">Desde ₡6.500</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Pickup / Express --}}
        <section class="home-reveal overflow-hidden rounded-[28px] sm:rounded-[32px] bg-blue-500 px-6 py-8 sm:px-10 sm:py-10 lg:px-14 lg:py-12 text-white">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-[-0.04em]">¿Pickup o Express?<br>Vos decidís.</h2>
                    <p class="mt-5 max-w-xl text-sm sm:text-base leading-7 text-blue-100">Elegí la sucursal más cercana o pedí entrega. Te mostramos cobertura, distancia y tiempo estimado antes de confirmar.</p>
                    <a href="/?cambiar_entrega=1" data-delivery-selector class="mt-7 inline-flex rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-blue-600 shadow-lg transition hover:-translate-y-0.5">
                        Elegir tipo de entrega
                    </a>
                </div>
                <div class="relative min-h-[250px] overflow-hidden rounded-[24px] sm:rounded-[28px] bg-[#EEF6FF] p-6 text-slate-950">
                    <div class="absolute left-[16%] top-[31%] h-4 w-4 rounded-full bg-blue-500 ring-4 ring-blue-200"></div>
                    <div class="absolute left-[45%] top-[39%] h-4 w-4 rounded-full bg-red-500 ring-4 ring-red-200"></div>
                    <div class="absolute left-[72%] top-[57%] h-4 w-4 rounded-full bg-blue-500 ring-4 ring-blue-200"></div>
                    <div class="absolute left-[33%] top-[70%] h-4 w-4 rounded-full bg-blue-500 ring-4 ring-blue-200"></div>
                    <div class="absolute left-[21%] top-[48%] h-px w-[62%] rotate-[10deg] bg-blue-300"></div>
                    <div class="absolute left-[29%] top-[58%] h-px w-[48%] -rotate-[14deg] bg-red-300"></div>
                    <p class="absolute bottom-5 left-6 text-xs font-semibold text-slate-700">7 sucursales • cobertura calculada en tiempo real</p>
                </div>
            </div>
        </section>
    </div>
</div>

@include('catalogo.partials.modal')
@include('catalogo.partials.modal_promocion')

@once
<div
    x-data="deliveryModal"
    x-show="abierto"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#071426]/70 p-4 backdrop-blur-sm"
>
    <div class="w-full max-w-md rounded-[28px] border border-white/50 bg-white p-7 sm:p-8 text-center shadow-2xl">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-blue-50 text-2xl">🍕</span>
        <h2 class="mt-5 text-2xl font-bold text-slate-950">{{ __('catalogo.como_recibir_pedido') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('catalogo.selecciona_opcion') }}</p>

        <div class="mt-7 grid gap-3 sm:grid-cols-2">
            <button type="button" @click="choose('pickup')" class="rounded-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-5 transition">
                {{ __('catalogo.para_llevar') }}
            </button>
            <button type="button" @click="choose('express')" class="rounded-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-5 transition">
                {{ __('catalogo.express') }}
            </button>
        </div>
    </div>
</div>
@endonce
@endsection
