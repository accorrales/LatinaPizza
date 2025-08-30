<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Modal Escoger Express o llevar --}}
    <style>[x-cloak]{display:none!important}</style>

    <style>
        html, body { height: 100%; }
        body { display:flex; flex-direction:column; min-height:100vh; }
        main { flex:1; }
        @keyframes fadeIn { from {opacity:0; transform:scale(0.95);} to {opacity:1; transform:scale(1);} }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fade-in-down { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
        .animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 font-sans text-gray-900">
<header class="bg-white backdrop-blur shadow-md sticky top-0 z-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-4 py-3">

        {{-- LOGO --}}
        <a href="/" class="flex items-center">
            <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}"
                 class="h-16 w-auto transition-transform duration-300 hover:scale-105 drop-shadow-lg">
        </a>

        {{-- BOTÓN HAMBURGUESA --}}
        <button id="menu-toggle"
                class="sm:hidden flex flex-col justify-center items-center w-8 h-8 space-y-1 focus:outline-none"
                aria-label="{{ __('layout.toggle_nav') }}">
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
            <span class="hamburger-line w-6 h-0.5 bg-red-600 transition-all duration-300"></span>
        </button>

        {{-- NAVIGATION EN ESCRITORIO --}}
        <nav id="main-menu"
             class="hidden sm:flex flex-wrap items-center gap-5 text-sm sm:text-base font-medium text-gray-800">
            <a href="{{ route('carrito.ver') }}"
               class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                🛒
                @if(isset($carritoCount) && $carritoCount > 0)
                    <span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full px-1 font-bold shadow">
                        {{ $carritoCount }}
                    </span>
                @endif
            </a>

            <a href="/catalogo"
               class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                🍕 {{ __('layout.menu') }}
            </a>
            <a  href="/?cambiar_entrega=1"
                    onclick="event.preventDefault(); if (window.abrirSelectorEntrega) { window.abrirSelectorEntrega(); } else { window.location.href='/?cambiar_entrega=1'; }"
                    class="relative inline-flex items-center gap-2 hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">

                    <span class="text-base"></span>
                    <span class="hidden sm:inline">Tipo Entrega</span>

                    {{-- (Opcional) chip con selección actual leída de localStorage --}}
                    <span x-data="{ t: localStorage.getItem('tipo_pedido') || '' }"
                        x-cloak
                        class="ml-1 text-xs px-2 py-0.5 rounded-full border"
                        :class="t==='express' ? 'border-blue-500 text-blue-600' : (t==='pickup' ? 'border-red-500 text-red-600' : 'border-gray-300 text-gray-500')"
                        x-text="t==='express' ? 'Express' : (t==='pickup' ? 'Pickup' : 'Elegir')">
                    </span>
                </a>
            @auth
                <a href="{{ route('usuario.pedidos') }}"
                   class="relative hover:text-red-700 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                    🧾 {{ __('layout.my_orders') }}
                </a>

                @if(Auth::user()->role === 'admin')
                    {{-- MENÚ DESPLEGABLE ADMIN --}}
                    <div x-data="{ open: false }" class="relative">
                        <div @mouseenter="open = true" @mouseleave="open = false" class="relative">
                            <button class="hover:text-blue-700 transition flex items-center gap-1">
                                🛠️ {{ __('layout.admin') }} <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div x-show="open" x-transition
                                 class="absolute bg-white shadow-lg rounded-md mt-2 py-2 px-3 w-48 z-50 border border-gray-200">
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
                                <a href="{{ route('kitchen.index') }}"
                                    class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                                    👩‍🍳 Cocina
                                </a>
                                <a href="{{ route('admin.ventas') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">
                                📈 Ventas
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- USUARIO --}}
                <span class="text-sm sm:text-base text-gray-700">
                    {{ __('layout.hello_user', ['name' => Auth::user()->name]) }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                        {{ __('layout.logout') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-blue-700 transition">🔐 {{ __('layout.login') }}</a>
            @endauth

            {{-- Idiomas --}}
            <div class="flex gap-3 items-center">
                <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}"
                   class="text-sm hover:underline {{ app()->getLocale() === 'es' ? 'font-bold text-red-600' : '' }}">
                    🇪🇸 {{ __('layout.spanish') }}
                </a>
                <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}"
                   class="text-sm hover:underline {{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : '' }}">
                    🇺🇸 {{ __('layout.english') }}
                </a>
            </div>
        </nav>
    </div>

    {{-- MENÚ PARA MÓVIL --}}
    <div id="mobile-menu"
         class="sm:hidden hidden flex flex-col gap-3 px-4 pb-4 text-sm text-gray-800 font-medium animate-slide-down">
        <a href="/catalogo" class="hover:text-red-600 transition">🍕 {{ __('layout.menu') }}</a>
        <a href="{{ route('carrito.ver') }}" class="hover:text-red-600 transition">🛒 {{ __('layout.cart') }}</a>
        <a  href="/?cambiar_entrega=1"
            onclick="event.preventDefault(); if (window.abrirSelectorEntrega) { window.abrirSelectorEntrega(); } else { window.location.href='/?cambiar_entrega=1'; }"
            class="hover:text-red-600 transition">
            Tipo Entrega
            <span x-data="{ t: localStorage.getItem('tipo_pedido') || '' }"
                x-cloak
                class="ml-2 text-xs px-2 py-0.5 rounded-full border align-middle"
                :class="t==='express' ? 'border-blue-500 text-blue-600' : (t==='pickup' ? 'border-red-500 text-red-600' : 'border-gray-300 text-gray-500')"
                x-text="t==='express' ? 'Express' : (t==='pickup' ? 'Pickup' : 'Elegir')">
            </span>
        </a>
        @auth
            <a href="{{ route('usuario.pedidos') }}" class="hover:text-blue-700 transition">🧾 {{ __('layout.my_orders') }}</a>
            @auth
                @if(in_array(Auth::user()->role, ['admin','cocina']))
                    <a href="{{ route('kitchen.index') }}"
                    class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                    👩‍🍳 Cocina
                    </a>
                @endif
            @endauth
            @if(Auth::user()->role === 'admin')
            
                {{-- 🛠️ Mantenimientos --}}
                <div x-data="{ openAdmin: false }" class="relative">
                    <button @click="openAdmin = !openAdmin"
                            class="hover:text-blue-700 transition flex items-center gap-1">
                        🛠️ {{ __('layout.admin') }}
                        <i :class="openAdmin ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-xs"></i>
                    </button>
                    <div x-show="openAdmin" @click.away="openAdmin = false" x-transition
                         class="absolute right-0 mt-2 bg-white shadow-md rounded-md z-50 w-56 border border-gray-200 py-2 text-sm text-gray-800">
                        <a href="{{ route('admin.usuarios.index') }}" class="block px-4 py-2 hover:bg-gray-100">👥 {{ __('layout.users') }}</a>
                        <a href="{{ route('admin.productos.index') }}" class="block px-4 py-2 hover:bg-gray-100">🧀 {{ __('layout.products') }}</a>
                        <a href="{{ route('admin.categorias.index') }}" class="block px-4 py-2 hover:bg-gray-100">⚙️ {{ __('layout.categories') }}</a>
                        <a href="{{ route('admin.sabores.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.flavors') }}</a>
                        <a href="{{ route('admin.tamanos.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.sizes') }}</a>
                        <a href="{{ route('admin.masas.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.doughs') }}</a>
                        <a href="{{ route('admin.extras.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.extras') }}</a>
                        <a href="{{ route('admin.promociones.index') }}" class="block px-4 py-2 hover:bg-gray-100">{{ __('layout.promos') }}</a>
                        <hr class="my-1">
                        <a href="{{ route('admin.pedidos.index') }}" class="block px-4 py-2 hover:bg-gray-100">📋 {{ __('layout.orders') }}</a>
                        <a href="{{ url('/admin/tiempo-estimado') }}" class="block px-4 py-2 hover:bg-gray-100">⏱️ {{ __('layout.eta') }}</a>
                        <a href="{{ url('/admin/resumen-sucursal/' . Auth::user()->sucursal_id) }}" class="block px-4 py-2 hover:bg-gray-100">📊 {{ __('layout.branch_summary') }}</a>
                        <a href="{{ route('admin.ventas') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">
                        📈 Ventas
                        </a>
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

        {{-- Idiomas móvil --}}
        <div class="flex gap-3 items-center">
            <a href="{{ route('cambiar_idioma', ['locale' => 'es']) }}"
               class="text-sm hover:underline {{ app()->getLocale() === 'es' ? 'font-bold text-red-600' : '' }}">
                🇪🇸 {{ __('layout.spanish') }}
            </a>
            <a href="{{ route('cambiar_idioma', ['locale' => 'en']) }}"
               class="text-sm hover:underline {{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : '' }}">
                🇺🇸 {{ __('layout.english') }}
            </a>
        </div>
        
    </div>

    {{-- SCRIPTS INTERACTIVOS --}}
    <script>
        const toggle = document.getElementById('menu-toggle');
        const menu = document.getElementById('mobile-menu');
        const lines = toggle.querySelectorAll('.hamburger-line');

        toggle.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            lines[0].classList.toggle('rotate-45');
            lines[1].classList.toggle('opacity-0');
            lines[2].classList.toggle('-rotate-45');
        });

        // Cerrar el menú móvil al hacer clic en un enlace
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
                lines[0].classList.remove('rotate-45');
                lines[1].classList.remove('opacity-0');
                lines[2].classList.remove('-rotate-45');
            });
        });
    </script>

    <style>
        .animate-slide-down { animation: slideDown 0.3s ease-out forwards; }
        @keyframes slideDown { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
        .group:hover .group-hover\:flex { display: flex !important; }
    </style>
</header>

{{-- 🍕 Overlay de Carga Visual --}}
<div id="loadingOverlay" class="fixed inset-0 z-[9999] bg-black bg-opacity-50 backdrop-blur-sm hidden flex items-center justify-center transition-opacity duration-300">
    <div class="flex flex-col items-center space-y-4 animate-fade-in">
        <div class="relative">
            <img src="/images/pizzaloading.gif" alt="{{ __('layout.loading_alt') }}" class="w-24 h-24 animate-spin-slow drop-shadow-glow">
            <div class="absolute inset-0 rounded-full bg-red-500 opacity-30 blur-2xl animate-ping"></div>
        </div>
        <p class="text-white text-base sm:text-lg font-medium tracking-wide animate-pulse">{{ __('layout.loading_love') }}</p>
    </div>
</div>

<style>
    .animate-spin-slow { animation: spin 1.8s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
    function mostrarLoading() { document.getElementById('loadingOverlay').classList.remove('hidden'); }
    function ocultarLoading() { document.getElementById('loadingOverlay').classList.add('hidden'); }
    function validarYMostrarLoading(formId) {
        const form = document.getElementById(formId);
        if (form && form.checkValidity()) { mostrarLoading(); return true; }
        form?.reportValidity(); return false;
    }
</script>

<main class="max-w-7xl mx-auto py-8">
    @yield('content')
</main>

<footer class="bg-gray-900 text-white py-10 mt-16 px-4" data-aos="fade-up">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-10">
        {{-- LOGO + DESCRIPCIÓN --}}
        <div class="flex flex-col items-start space-y-4">
            <img src="{{ asset('images/Logo.png') }}" alt="{{ __('layout.logo_alt') }}"
                 class="h-14 w-auto drop-shadow-lg transition-transform duration-300 hover:scale-105">
            <p class="text-sm text-gray-400 leading-6">
                {{ __('layout.brand_tagline') }}
            </p>
        </div>

        {{-- ENLACES RÁPIDOS --}}
        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">{{ __('layout.links') }}</h4>
            <a href="/" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.home') }}</a>
            <a href="/catalogo" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.menu') }}</a>
            <a href="{{ route('usuario.pedidos') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.my_orders') }}</a>
            @auth
                <a href="{{ route('logout') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.logout') }}</a>
            @else
                <a href="{{ route('login') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.login') }}</a>
                <a href="{{ route('register') }}" class="block text-gray-300 hover:text-red-300 transition">{{ __('layout.register') }}</a>
            @endauth
        </div>

        {{-- CONTACTO + HORARIOS --}}
        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">{{ __('layout.contact') }}</h4>
            <p class="text-sm text-gray-400">{{ __('layout.address') }}</p>
            <p class="text-sm text-gray-400">{{ __('layout.phone') }}</p>
            <p class="text-sm text-gray-400">{{ __('layout.email') }}</p>

            <h4 class="text-red-400 font-semibold mt-5 mb-1 text-lg">{{ __('layout.hours') }}</h4>
            <p class="text-sm text-gray-400">{{ __('layout.hours_label') }}</p>
            <p class="text-sm text-gray-500 italic">{{ __('layout.hours_note') }}</p>

            <div class="flex space-x-4 mt-4">
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <div class="mt-10 text-center text-gray-500 text-sm border-t border-gray-800 pt-4">
        © {{ date('Y') }} {{ config('app.name') }}. {{ __('layout.footer_copy') }}
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true, easing: 'ease-in-out' });
</script>

@yield('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script> window.isAuthenticated = @json(Auth::check()); </script>
@stack('scripts')
<script>
function entregaModal() {
  const API_BASE = @json(rtrim(config('services.latina_api.base_url'), '/')); // p.ej. http://127.0.0.1:8001
  return {
    abierto: false,

    async init() {
      // Forzar apertura con ?cambiar_entrega=1 (o ?cambiar=1 / ?force=1)
      const params = new URLSearchParams(window.location.search);
      const force = params.has('cambiar_entrega') || params.has('cambiar') || params.get('force') === '1';

      const hasChoice       = !!localStorage.getItem('tipo_pedido');
      const pending         = localStorage.getItem('pending_tipo_pedido');
      const pendingRedirect = localStorage.getItem('pending_redirect');

      // Si venimos del login con elección pendiente: persistir y redirigir sin mostrar modal
      if (pending && window.isAuthenticated) {
        await this.persistTipo(pending);
        localStorage.removeItem('pending_tipo_pedido');

        const url = pendingRedirect || (pending === 'pickup' ? '/pickup' : '/express');
        localStorage.removeItem('pending_redirect');

        window.location.replace(url);
        return;
      }

      // Mostrar si no hay elección o si se fuerza por query param
      this.abierto = force || !hasChoice;
    },

    async choose(tipo) {
      const urlDestino = (tipo === 'pickup') ? '/pickup' : '/express';

      if (window.isAuthenticated) {
        await this.persistTipo(tipo);
        this.abierto = false;
        window.location.assign(urlDestino);
      } else {
        // Guardar intención y mandar a login
        localStorage.setItem('pending_tipo_pedido', tipo);
        localStorage.setItem('pending_redirect', urlDestino);
        window.location.assign('/login');
      }
    },

    async persistTipo(tipo) {
      try {
        await fetch(`${API_BASE}/guardar-tipo-pedido`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' // ← FIX
          },
          credentials: 'include',
          body: JSON.stringify({ tipo })
        });
        localStorage.setItem('tipo_pedido', tipo);
      } catch (e) {
        console.warn('No se pudo guardar tipo_pedido en backend:', e);
        // Igual guardamos en localStorage para no volver a mostrar
        localStorage.setItem('tipo_pedido', tipo);
      }
    }
  }
}

// (Opcional) función global para “cambiar” después:
// llama a abrirSelectorEntrega() desde un botón/link en el header
window.abrirSelectorEntrega = () => {
  localStorage.removeItem('tipo_pedido');
  const url = new URL(window.location.href);
  url.searchParams.set('cambiar_entrega', '1');
  window.location.href = url.toString();
};
</script>

</body>
</html>

