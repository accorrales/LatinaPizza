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
<body class="bg-gray-100 font-sans text-gray-900" data-authenticated="{{ Auth::check() ? '1' : '0' }}">
<header class="bg-white backdrop-blur shadow-md sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-4 py-3">
        <a href="/" class="flex items-center">
            <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}"
                 class="h-16 w-auto transition-transform duration-300 hover:scale-105 drop-shadow-lg">
        </a>

        <button id="menu-toggle"
                type="button"
                class="sm:hidden flex flex-col justify-center items-center w-8 h-8 space-y-1 focus:outline-none"
                aria-label="{{ __('layout.toggle_nav') }}">
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
        </button>

        <nav id="main-menu" class="hidden sm:flex flex-wrap items-center gap-5 text-sm sm:text-base font-medium text-gray-800">
            <a href="{{ route('carrito.ver') }}" class="relative hover:text-red-600 transition">
                🛒
                @if(isset($carritoCount) && $carritoCount > 0)
                    <span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full px-1 font-bold shadow">{{ $carritoCount }}</span>
                @endif
            </a>

            <a href="/catalogo" class="hover:text-red-600 transition">🍕 {{ __('layout.menu') }}</a>

            <a href="/?cambiar_entrega=1" data-delivery-selector class="inline-flex items-center gap-2 hover:text-red-600 transition">
                <span class="hidden sm:inline">Tipo Entrega</span>
                <span x-data="deliveryChip"
                      x-cloak
                      class="ml-1 text-xs px-2 py-0.5 rounded-full border"
                      :class="t === 'express' ? 'border-blue-500 text-blue-600' : (t === 'pickup' ? 'border-red-500 text-red-600' : 'border-gray-300 text-gray-500')"
                      x-text="t === 'express' ? 'Express' : (t === 'pickup' ? 'Pickup' : 'Elegir')"></span>
            </a>

            @auth
                <a href="{{ route('usuario.pedidos') }}" class="hover:text-red-700 transition">🧾 {{ __('layout.my_orders') }}</a>

                @if(Auth::user()->role === 'admin')
                    <div x-data="{ open: false }" class="relative">
                        <div @mouseenter="open = true" @mouseleave="open = false" class="relative">
                            <button type="button" class="hover:text-blue-700 transition flex items-center gap-1">
                                🛠️ {{ __('layout.admin') }} <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div x-show="open" x-transition class="absolute bg-white shadow-lg rounded-md mt-2 py-2 px-3 w-48 z-50 border border-gray-200">
                                <a href="{{ route('admin.usuarios.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">👥 {{ __('layout.users') }}</a>
                                <a href="{{ route('admin.productos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">🧀 {{ __('layout.products') }}</a>
                                <a href="{{ route('admin.categorias.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">⚙️ {{ __('layout.categories') }}</a>
                                <a href="{{ route('admin.pedidos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">📦 {{ __('layout.orders') }}</a>
                                <a href="{{ route('admin.sabores.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.flavors') }}</a>
                                <a href="{{ route('admin.tamanos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.sizes') }}</a>
                                <a href="{{ route('admin.masas.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.doughs') }}</a>
                                <a href="{{ route('admin.extras.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.extras') }}</a>
                                <a href="{{ route('admin.resenas.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.reviews') }}</a>
                                <a href="{{ route('admin.promociones.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">{{ __('layout.promos') }}</a>
                                <a href="{{ route('kitchen.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">👩‍🍳 Cocina</a>
                                <a href="{{ route('admin.ventas') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">📈 Ventas</a>
                            </div>
                        </div>
                    </div>
                @endif

                <span class="text-sm sm:text-base text-gray-700">{{ __('layout.hello_user', ['name' => Auth::user()->name]) }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-600 transition">{{ __('layout.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-blue-700 transition">🔐 {{ __('layout.login') }}</a>
            @endauth

            <div class="flex gap-3 items-center">
                <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}" class="text-sm hover:underline {{ app()->getLocale() === 'es' ? 'font-bold text-red-600' : '' }}">🇪🇸 {{ __('layout.spanish') }}</a>
                <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}" class="text-sm hover:underline {{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : '' }}">🇺🇸 {{ __('layout.english') }}</a>
            </div>
        </nav>
    </div>

    <div id="mobile-menu" class="sm:hidden hidden flex flex-col gap-3 px-4 pb-4 text-sm text-gray-800 font-medium animate-slide-down">
        <a href="/catalogo" class="hover:text-red-600 transition">🍕 {{ __('layout.menu') }}</a>
        <a href="{{ route('carrito.ver') }}" class="hover:text-red-600 transition">🛒 {{ __('layout.cart') }}</a>
        <a href="/?cambiar_entrega=1" data-delivery-selector class="hover:text-red-600 transition">
            Tipo Entrega
            <span x-data="deliveryChip"
                  x-cloak
                  class="ml-2 text-xs px-2 py-0.5 rounded-full border align-middle"
                  :class="t === 'express' ? 'border-blue-500 text-blue-600' : (t === 'pickup' ? 'border-red-500 text-red-600' : 'border-gray-300 text-gray-500')"
                  x-text="t === 'express' ? 'Express' : (t === 'pickup' ? 'Pickup' : 'Elegir')"></span>
        </a>

        @auth
            <a href="{{ route('usuario.pedidos') }}" class="hover:text-blue-700 transition">🧾 {{ __('layout.my_orders') }}</a>

            @if(in_array(Auth::user()->role, ['admin', 'cocina']))
                <a href="{{ route('kitchen.index') }}" class="hover:text-red-600 transition">👩‍🍳 Cocina</a>
            @endif

            @if(Auth::user()->role === 'admin')
                <div x-data="{ openAdmin: false }" class="relative">
                    <button type="button" @click="openAdmin = !openAdmin" class="hover:text-blue-700 transition flex items-center gap-1">
                        🛠️ {{ __('layout.admin') }}
                        <i :class="openAdmin ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-xs"></i>
                    </button>
                    <div x-show="openAdmin" @click.away="openAdmin = false" x-transition class="mt-2 bg-white shadow-md rounded-md w-56 border border-gray-200 py-2 text-sm text-gray-800">
                        <a href="{{ route('admin.usuarios.index') }}" class="block px-4 py-2 hover:bg-gray-100">👥 {{ __('layout.users') }}</a>
                        <a href="{{ route('admin.productos.index') }}" class="block px-4 py-2 hover:bg-gray-100">🧀 {{ __('layout.products') }}</a>
                        <a href="{{ route('admin.categorias.index') }}" class="block px-4 py-2 hover:bg-gray-100">⚙️ {{ __('layout.categories') }}</a>
                        <a href="{{ route('admin.sabores.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.flavors') }}</a>
                        <a href="{{ route('admin.tamanos.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.sizes') }}</a>
                        <a href="{{ route('admin.masas.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.doughs') }}</a>
                        <a href="{{ route('admin.extras.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.extras') }}</a>
                        <a href="{{ route('admin.promociones.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.promos') }}</a>
                        <a href="{{ route('admin.pedidos.index') }}" class="block px-4 py-2 hover:bg-gray-100">📋 {{ __('layout.orders') }}</a>
                        <a href="{{ route('admin.ventas') }}" class="block px-4 py-2 hover:bg-gray-100">📈 Ventas</a>
                    </div>
                </div>
            @endif

            <span class="text-gray-700 mt-3">{{ __('layout.hello_user', ['name' => Auth::user()->name]) }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:text-red-600 transition">{{ __('layout.logout') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-blue-700 transition">🔐 {{ __('layout.login') }}</a>
            <a href="{{ route('register') }}" class="hover:text-blue-700 transition">📝 {{ __('layout.register') }}</a>
        @endauth

        <div class="flex gap-3 items-center">
            <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}" class="text-sm hover:underline {{ app()->getLocale() === 'es' ? 'font-bold text-red-600' : '' }}">🇪🇸 {{ __('layout.spanish') }}</a>
            <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}" class="text-sm hover:underline {{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : '' }}">🇺🇸 {{ __('layout.english') }}</a>
        </div>
    </div>
</header>

<div id="loadingOverlay" class="fixed inset-0 z-[9999] bg-black bg-opacity-50 backdrop-blur-sm hidden flex items-center justify-center transition-opacity duration-300">
    <div class="flex flex-col items-center space-y-4 animate-fade-in">
        <div class="relative">
            <img src="/images/pizzaloading.gif" alt="{{ __('layout.loading_alt') }}" class="w-24 h-24 animate-spin-slow drop-shadow-glow">
            <div class="absolute inset-0 rounded-full bg-red-500 opacity-30 blur-2xl animate-ping"></div>
        </div>
        <p class="text-white text-base sm:text-lg font-medium tracking-wide animate-pulse">{{ __('layout.loading_love') }}</p>
    </div>
</div>

<main class="max-w-7xl mx-auto py-8">
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset
</main>

<footer class="bg-gray-900 text-white py-10 mt-16 px-4" data-aos="fade-up">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-10">
        <div class="flex flex-col items-start space-y-4">
            <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}" class="h-14 w-auto drop-shadow-lg transition-transform duration-300 hover:scale-105">
            <p class="text-sm text-gray-400 leading-6">{{ __('layout.brand_tagline') }}</p>
        </div>

        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">{{ __('layout.links') }}</h4>
            <a href="/" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.home') }}</a>
            <a href="/catalogo" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.menu') }}</a>
            @auth
                <a href="{{ route('usuario.pedidos') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.my_orders') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.login') }}</a>
                <a href="{{ route('register') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.register') }}</a>
            @endauth
        </div>

        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">{{ __('layout.contact') }}</h4>
            @if(config('business.address'))<p class="text-sm text-gray-400">📍 {{ config('business.address') }}</p>@endif
            @if(config('business.phone'))<p class="text-sm text-gray-400">📞 {{ config('business.phone') }}</p>@endif
            @if(config('business.email'))<p class="text-sm text-gray-400">✉️ {{ config('business.email') }}</p>@endif

            <h4 class="text-red-400 font-semibold mt-5 mb-1 text-lg">{{ __('layout.hours') }}</h4>
            <p class="text-sm text-gray-400">{{ __('layout.hours_label') }}</p>
            <p class="text-sm text-gray-500 italic">{{ __('layout.hours_note') }}</p>

            @if(config('business.facebook_url') || config('business.instagram_url') || config('business.whatsapp_url'))
                <div class="flex space-x-4 mt-4">
                    @if(config('business.facebook_url'))<a href="{{ config('business.facebook_url') }}" rel="noopener noreferrer" target="_blank" aria-label="Facebook" class="text-gray-400 hover:text-red-400"><i class="fab fa-facebook-f"></i></a>@endif
                    @if(config('business.instagram_url'))<a href="{{ config('business.instagram_url') }}" rel="noopener noreferrer" target="_blank" aria-label="Instagram" class="text-gray-400 hover:text-red-400"><i class="fab fa-instagram"></i></a>@endif
                    @if(config('business.whatsapp_url'))<a href="{{ config('business.whatsapp_url') }}" rel="noopener noreferrer" target="_blank" aria-label="WhatsApp" class="text-gray-400 hover:text-red-400"><i class="fab fa-whatsapp"></i></a>@endif
                </div>
            @endif
        </div>
    </div>

    <div class="mt-10 text-center text-gray-500 text-sm border-t border-gray-800 pt-4">
        © {{ date('Y') }} {{ config('app.name') }}. {{ __('layout.footer_copy') }}
    </div>
</footer>

@yield('scripts')
@stack('scripts')
</body>
</html>
