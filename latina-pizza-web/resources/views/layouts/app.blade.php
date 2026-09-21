<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ trim($__env->yieldContent('meta_description', 'Ordená pizzas, promociones y entrega express con Latina Pizza.')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html, body { height: 100%; }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .animate-fade-in { animation: fadeIn .3s ease-out; }
        .animate-fade-in-down { animation: fadeInDown .3s ease-out; }
        .animate-slide-down { animation: slideDown .3s ease-out forwards; }
        .animate-spin-slow { animation: spin 1.8s linear infinite; }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased" data-authenticated="{{ Auth::check() ? '1' : '0' }}">
<header id="site-header" class="site-header sticky top-0 z-50 border-b border-transparent bg-white/95 backdrop-blur-xl">
    <div class="mx-auto flex h-[76px] max-w-[1296px] items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="Latina Pizza - Inicio">
            <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}" class="h-11 w-auto object-contain transition duration-300 hover:scale-[1.03]">
        </a>

        <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-700" aria-label="Navegación principal">
            <a href="{{ route('home') }}" class="transition hover:text-blue-600 {{ request()->routeIs('home') ? 'text-blue-600' : '' }}">Inicio</a>
            <a href="{{ route('catalogo.index') }}" class="transition hover:text-blue-600 {{ request()->routeIs('catalogo.*') ? 'text-blue-600' : '' }}">{{ __('layout.menu') }}</a>
            <a href="{{ route('home') }}#promociones" class="transition hover:text-blue-600">Promociones</a>
            <a href="/?cambiar_entrega=1" data-delivery-selector class="transition hover:text-blue-600">Sucursales</a>
        </nav>

        <div class="hidden lg:flex items-center gap-2.5">
            <a href="/?cambiar_entrega=1" data-delivery-selector class="inline-flex h-10 items-center gap-2 rounded-full bg-[#F6F9FF] px-4 text-xs font-semibold text-blue-600 transition hover:bg-blue-50">
                <i class="fas fa-location-dot text-[11px]"></i>
                <span>Entrega</span>
                <span x-data="deliveryChip"
                      x-cloak
                      class="rounded-full border px-2 py-0.5 text-[10px]"
                      :class="t === 'express' ? 'border-blue-300 text-blue-600' : (t === 'pickup' ? 'border-red-300 text-red-600' : 'border-slate-300 text-slate-500')"
                      x-text="t === 'express' ? 'Express' : (t === 'pickup' ? 'Pickup' : 'Elegir')"></span>
            </a>

            <a href="{{ route('carrito.ver') }}" class="relative grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:text-blue-600" aria-label="{{ __('layout.cart') }}">
                <i class="fas fa-cart-shopping text-sm"></i>
                @if(isset($carritoCount) && $carritoCount > 0)
                    <span class="absolute -right-1 -top-1 grid min-h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow">{{ $carritoCount }}</span>
                @endif
            </a>

            @auth
                <a href="{{ route('usuario.pedidos') }}" class="grid h-10 w-10 place-items-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:text-blue-600" aria-label="{{ __('layout.my_orders') }}">
                    <i class="fas fa-receipt text-sm"></i>
                </a>

                @if(Auth::user()->role === 'admin')
                    <details class="group relative">
                        <summary class="flex h-10 cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                            <i class="fas fa-sliders text-[11px]"></i>
                            Admin
                            <i class="fas fa-chevron-down text-[9px] transition group-open:rotate-180"></i>
                        </summary>
                        <div class="absolute right-0 top-12 z-50 grid w-64 grid-cols-2 gap-1 rounded-2xl border border-slate-200 bg-white p-3 text-xs font-medium text-slate-700 shadow-2xl">
                            <a href="{{ route('admin.usuarios.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.users') }}</a>
                            <a href="{{ route('admin.productos.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.products') }}</a>
                            <a href="{{ route('admin.categorias.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.categories') }}</a>
                            <a href="{{ route('admin.pedidos.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.orders') }}</a>
                            <a href="{{ route('admin.sabores.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.flavors') }}</a>
                            <a href="{{ route('admin.tamanos.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.sizes') }}</a>
                            <a href="{{ route('admin.masas.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.doughs') }}</a>
                            <a href="{{ route('admin.extras.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.extras') }}</a>
                            <a href="{{ route('admin.resenas.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.reviews') }}</a>
                            <a href="{{ route('admin.promociones.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.promos') }}</a>
                            <a href="{{ route('kitchen.index') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">Cocina</a>
                            <a href="{{ route('admin.ventas') }}" class="rounded-xl px-3 py-2.5 hover:bg-slate-50 hover:text-blue-600">Ventas</a>
                        </div>
                    </details>
                @elseif(Auth::user()->role === 'cocina')
                    <a href="{{ route('kitchen.index') }}" class="rounded-full border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-600">Cocina</a>
                @endif

                <details class="group relative">
                    <summary class="flex h-10 max-w-[170px] cursor-pointer list-none items-center gap-2 rounded-full bg-[#071426] px-4 text-xs font-semibold text-white transition hover:bg-slate-800">
                        <span class="truncate">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down text-[9px] transition group-open:rotate-180"></i>
                    </summary>
                    <div class="absolute right-0 top-12 z-50 w-48 rounded-2xl border border-slate-200 bg-white p-2 text-sm shadow-2xl">
                        <a href="{{ route('usuario.pedidos') }}" class="block rounded-xl px-3 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-blue-600">{{ __('layout.my_orders') }}</a>
                        <div class="my-1 h-px bg-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-red-600 hover:bg-red-50">{{ __('layout.logout') }}</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-full border border-slate-200 bg-white px-4 text-xs font-semibold text-blue-600 transition hover:border-blue-200 hover:bg-blue-50">{{ __('layout.login') }}</a>
                <a href="{{ route('catalogo.index') }}" class="inline-flex h-10 items-center rounded-full bg-red-500 px-5 text-xs font-semibold text-white shadow-lg shadow-red-950/10 transition hover:bg-red-600">Pedir ahora</a>
            @endauth

            <div class="ml-1 flex items-center gap-1 rounded-full border border-slate-200 bg-white p-1 text-[10px] font-bold">
                <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}" class="rounded-full px-2 py-1.5 transition {{ app()->getLocale() === 'es' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-blue-600' }}">ES</a>
                <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}" class="rounded-full px-2 py-1.5 transition {{ app()->getLocale() === 'en' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-blue-600' }}">EN</a>
            </div>
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            <a href="{{ route('carrito.ver') }}" class="relative grid h-10 w-10 place-items-center rounded-full border border-slate-200 text-slate-700" aria-label="{{ __('layout.cart') }}">
                <i class="fas fa-cart-shopping text-sm"></i>
                @if(isset($carritoCount) && $carritoCount > 0)
                    <span class="absolute -right-1 -top-1 grid min-h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $carritoCount }}</span>
                @endif
            </a>
            <button id="menu-toggle" type="button" class="grid h-11 w-11 place-items-center rounded-full bg-[#071426]" aria-label="{{ __('layout.toggle_nav') }}" aria-expanded="false">
                <span class="relative block h-5 w-5">
                    <span class="hamburger-line absolute left-0 top-0.5 h-0.5 w-5 origin-center bg-white transition-all duration-300"></span>
                    <span class="hamburger-line absolute left-0 top-[9px] h-0.5 w-5 bg-white transition-all duration-300"></span>
                    <span class="hamburger-line absolute bottom-0.5 left-0 h-0.5 w-5 origin-center bg-white transition-all duration-300"></span>
                </span>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="fixed inset-0 z-[60] hidden lg:hidden" aria-hidden="true">
        <button type="button" data-mobile-menu-close class="absolute inset-0 h-full w-full bg-[#071426]/55 backdrop-blur-sm" aria-label="Cerrar menú"></button>
        <div data-mobile-menu-panel class="absolute right-0 top-0 flex h-full w-[min(88vw,380px)] translate-x-full flex-col overflow-y-auto bg-white p-6 shadow-2xl transition-transform duration-300 ease-out">
            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}" class="h-10 w-auto">
                <button type="button" data-mobile-menu-close class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-700" aria-label="Cerrar menú">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <nav class="mt-7 grid gap-2 text-base font-semibold text-slate-800" aria-label="Navegación móvil">
                <a href="{{ route('home') }}" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">Inicio</a>
                <a href="{{ route('catalogo.index') }}" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">{{ __('layout.menu') }}</a>
                <a href="{{ route('home') }}#promociones" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">Promociones</a>
                <a href="/?cambiar_entrega=1" data-delivery-selector class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">Tipo de entrega</a>
                <a href="{{ route('carrito.ver') }}" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">{{ __('layout.cart') }}</a>

                @auth
                    <a href="{{ route('usuario.pedidos') }}" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">{{ __('layout.my_orders') }}</a>
                    @if(in_array(Auth::user()->role, ['admin', 'cocina']))
                        <a href="{{ route('kitchen.index') }}" class="rounded-2xl px-4 py-3 hover:bg-blue-50 hover:text-blue-600">Cocina</a>
                    @endif
                    @if(Auth::user()->role === 'admin')
                        <details class="rounded-2xl border border-slate-200 p-2">
                            <summary class="cursor-pointer list-none rounded-xl px-3 py-2 text-sm font-semibold text-slate-700">Admin</summary>
                            <div class="mt-2 grid grid-cols-2 gap-1 text-xs font-medium text-slate-600">
                                <a href="{{ route('admin.usuarios.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.users') }}</a>
                                <a href="{{ route('admin.productos.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.products') }}</a>
                                <a href="{{ route('admin.categorias.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.categories') }}</a>
                                <a href="{{ route('admin.pedidos.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.orders') }}</a>
                                <a href="{{ route('admin.sabores.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.flavors') }}</a>
                                <a href="{{ route('admin.tamanos.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.sizes') }}</a>
                                <a href="{{ route('admin.masas.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.doughs') }}</a>
                                <a href="{{ route('admin.extras.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.extras') }}</a>
                                <a href="{{ route('admin.promociones.index') }}" class="rounded-xl p-2 hover:bg-slate-50">{{ __('layout.promos') }}</a>
                                <a href="{{ route('admin.ventas') }}" class="rounded-xl p-2 hover:bg-slate-50">Ventas</a>
                            </div>
                        </details>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full rounded-full bg-red-50 px-5 py-3 text-sm font-semibold text-red-600">{{ __('layout.logout') }}</button>
                    </form>
                @else
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <a href="{{ route('login') }}" class="rounded-full border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-blue-600">{{ __('layout.login') }}</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-red-500 px-5 py-3 text-center text-sm font-semibold text-white">{{ __('layout.register') }}</a>
                    </div>
                @endauth
            </nav>

            <div class="mt-auto pt-8">
                <a href="/?cambiar_entrega=1" data-delivery-selector class="flex items-center justify-between rounded-2xl bg-[#F6F9FF] p-4 text-sm font-semibold text-blue-600">
                    <span>Pickup / Express</span>
                    <span x-data="deliveryChip" x-cloak class="rounded-full border px-2 py-1 text-[10px]" :class="t === 'express' ? 'border-blue-300' : (t === 'pickup' ? 'border-red-300 text-red-600' : 'border-slate-300 text-slate-500')" x-text="t === 'express' ? 'Express' : (t === 'pickup' ? 'Pickup' : 'Elegir')"></span>
                </a>
                <div class="mt-4 flex gap-2 text-xs font-bold">
                    <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'es' ? 'bg-blue-500 text-white' : 'bg-slate-100 text-slate-600' }}">ES</a>
                    <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}" class="rounded-full px-3 py-2 {{ app()->getLocale() === 'en' ? 'bg-blue-500 text-white' : 'bg-slate-100 text-slate-600' }}">EN</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="loadingOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-[#071426]/70 backdrop-blur-sm transition-opacity duration-300">
    <div class="flex flex-col items-center space-y-4 animate-fade-in">
        <div class="relative">
            <img src="/images/pizzaloading.gif" alt="{{ __('layout.loading_alt') }}" class="h-24 w-24 animate-spin-slow drop-shadow-glow">
            <div class="absolute inset-0 animate-ping rounded-full bg-red-500 opacity-30 blur-2xl"></div>
        </div>
        <p class="text-base font-medium tracking-wide text-white sm:text-lg animate-pulse">{{ __('layout.loading_love') }}</p>
    </div>
</div>

<main class="{{ request()->routeIs('home') ? 'w-full py-8' : 'mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8' }}">
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset
</main>

<footer class="bg-[#071426] text-white {{ request()->routeIs('home') ? '' : 'mt-16' }}">
    <div class="mx-auto max-w-[1296px] px-5 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.35fr_.8fr_.8fr_1fr]">
            <div>
                <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}" class="h-12 w-auto object-contain brightness-0 invert">
                <p class="mt-5 max-w-xs text-sm leading-6 text-slate-400">{{ __('layout.brand_tagline') }}</p>
                <div class="mt-6 flex gap-3">
                    @if(config('business.facebook_url'))<a href="{{ config('business.facebook_url') }}" rel="noopener noreferrer" target="_blank" aria-label="Facebook" class="grid h-9 w-9 place-items-center rounded-full border border-white/10 text-slate-400 transition hover:border-blue-400 hover:text-blue-400"><i class="fab fa-facebook-f"></i></a>@endif
                    @if(config('business.instagram_url'))<a href="{{ config('business.instagram_url') }}" rel="noopener noreferrer" target="_blank" aria-label="Instagram" class="grid h-9 w-9 place-items-center rounded-full border border-white/10 text-slate-400 transition hover:border-red-400 hover:text-red-400"><i class="fab fa-instagram"></i></a>@endif
                    @if(config('business.whatsapp_url'))<a href="{{ config('business.whatsapp_url') }}" rel="noopener noreferrer" target="_blank" aria-label="WhatsApp" class="grid h-9 w-9 place-items-center rounded-full border border-white/10 text-slate-400 transition hover:border-emerald-400 hover:text-emerald-400"><i class="fab fa-whatsapp"></i></a>@endif
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">Explorá</h4>
                <div class="mt-5 grid gap-3 text-sm text-slate-400">
                    <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('layout.home') }}</a>
                    <a href="{{ route('catalogo.index') }}" class="transition hover:text-white">{{ __('layout.menu') }}</a>
                    <a href="{{ route('home') }}#promociones" class="transition hover:text-white">Promociones</a>
                    <a href="/?cambiar_entrega=1" data-delivery-selector class="transition hover:text-white">Pickup / Express</a>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">Ayuda</h4>
                <div class="mt-5 grid gap-3 text-sm text-slate-400">
                    @auth
                        <a href="{{ route('usuario.pedidos') }}" class="transition hover:text-white">{{ __('layout.my_orders') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="transition hover:text-white">{{ __('layout.login') }}</a>
                        <a href="{{ route('register') }}" class="transition hover:text-white">{{ __('layout.register') }}</a>
                    @endauth
                    @if(config('business.phone'))<span>{{ config('business.phone') }}</span>@endif
                    @if(config('business.email'))<span>{{ config('business.email') }}</span>@endif
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-white">{{ __('layout.contact') }}</h4>
                <div class="mt-5 grid gap-3 text-sm leading-6 text-slate-400">
                    @if(config('business.address'))<p>{{ config('business.address') }}</p>@endif
                    <p>{{ __('layout.hours_label') }}</p>
                    <p class="text-xs text-slate-500">{{ __('layout.hours_note') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ config('app.name') }}. {{ __('layout.footer_copy') }}</p>
            <p>Pedidos online • Pago seguro • Soporte</p>
        </div>
    </div>
</footer>

@yield('scripts')
@stack('scripts')
</body>
</html>
