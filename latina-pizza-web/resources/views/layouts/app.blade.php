<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Latina Pizza 🍕</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
    html, body {
        height: 100%;
    }
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    main {
        flex: 1;
    }
</style>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <header class="bg-white backdrop-blur shadow-md sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4 py-3">

            {{-- LOGO --}}
            <a href="/" class="flex items-center">
                <img src="{{ asset('storage/Logo.png') }}" alt="Latina Pizza Logo"
                    class="h-16 w-auto transition-transform duration-300 hover:scale-105 drop-shadow-lg">
            </a>

            {{-- BOTÓN HAMBURGUESA --}}
            <button id="menu-toggle"
                    class="sm:hidden flex flex-col justify-center items-center w-8 h-8 space-y-1 focus:outline-none"
                    aria-label="Toggle navigation">
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
                    @if($carritoCount > 0)
                        <span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full px-1 font-bold shadow">
                            {{ $carritoCount }}
                        </span>
                    @endif
                </a>
                <a href="/catalogo"
                class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">Menú</a>

                @auth
                    <a href="{{ route('usuario.pedidos') }}"
                    class="relative hover:text-red-700 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">Mis Pedidos</a>

                    @if(Auth::user()->role === 'admin')
                        {{-- MENÚ DESPLEGABLE ADMIN --}}
                        <div x-data="{ open: false }" class="relative">
                            <div @mouseenter="open = true" @mouseleave="open = false" class="relative">
                                <button class="hover:text-blue-700 transition flex items-center gap-1">
                                    🛠️ Administración <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                                <div x-show="open"
                                    x-transition
                                    class="absolute bg-white shadow-lg rounded-md mt-2 py-2 px-3 w-48 z-50 border border-gray-200">
                                    <a href="{{ route('admin.usuarios.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">👥 Usuarios</a>
                                    <a href="{{ route('admin.productos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">🧀 Productos</a>
                                    <a href="{{ route('admin.categorias.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">⚙️ Categorías</a>
                                    <a href="{{ route('admin.pedidos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">📦 Pedidos</a>
                                </div>
                            </div>
                            <div x-show="open"
                                @mouseenter="open = true"
                                @mouseleave="open = false"
                                x-transition
                                class="absolute bg-white shadow-lg rounded-md mt-2 py-2 px-3 w-48 z-50 border border-gray-200">
                                <a href="{{ route('admin.usuarios.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">👥 Usuarios</a>
                                <a href="{{ route('admin.productos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">🧀 Productos</a>
                                <a href="{{ route('admin.categorias.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">⚙️ Categorías</a>
                                <a href="{{ route('admin.pedidos.index') }}" class="block px-2 py-1 text-sm hover:bg-gray-100">📦 Pedidos</a>
                            </div>
                        </div>
                    @endif
                    {{-- USUARIO --}}

                    <span class="text-sm sm:text-base text-gray-700">👤 {{ Auth::user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="relative hover:text-red-600 transition after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-[2px] after:bg-red-600 hover:after:w-full after:transition-all after:duration-300">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-700 transition">🔐 Login</a>
                    <a href="{{ route('register') }}" class="hover:text-blue-700 transition">📝 Registro</a>
                @endauth
            </nav>
        </div>

        {{-- MENÚ PARA MÓVIL (animación slide) --}}
        <div id="mobile-menu"
            class="sm:hidden hidden flex flex-col gap-3 px-4 pb-4 text-sm text-gray-800 font-medium animate-slide-down">
            <a href="/catalogo" class="hover:text-red-600 transition">🍕 Menú</a>
            <a href="{{ route('carrito.ver') }}" class="hover:text-red-600 transition">🛒 Carrito</a>
            @auth
    <a href="{{ route('usuario.pedidos') }}" class="hover:text-blue-700 transition">🧾 Mis Pedidos</a>

    @if(Auth::user()->role === 'admin')

        {{-- 🛠️ Mantenimientos --}}
        <div x-data="{ openAdmin: false }" class="relative">
            <button @click="openAdmin = !openAdmin"
                    class="hover:text-blue-700 transition flex items-center gap-1">
                🛠️ Administración
                <i :class="openAdmin ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-xs"></i>
            </button>
            <div x-show="openAdmin"
                @click.away="openAdmin = false"
                x-transition
                class="absolute right-0 mt-2 bg-white shadow-md rounded-md z-50 w-56 border border-gray-200 py-2 text-sm text-gray-800">
                <a href="{{ route('admin.usuarios.index') }}" class="block px-4 py-2 hover:bg-gray-100">👥 Usuarios</a>
                <a href="{{ route('admin.productos.index') }}" class="block px-4 py-2 hover:bg-gray-100">🧀 Productos</a>
                <a href="{{ route('admin.categorias.index') }}" class="block px-4 py-2 hover:bg-gray-100">⚙️ Categorías</a>
                <hr class="my-1">
                <a href="{{ route('admin.pedidos.index') }}" class="block px-4 py-2 hover:bg-gray-100">📋 Pedidos</a>
                <a href="{{ url('/admin/tiempo-estimado') }}" class="block px-4 py-2 hover:bg-gray-100">⏱️ Tiempo Estimado</a>
                <a href="{{ url('/admin/resumen-sucursal/' . Auth::user()->sucursal_id) }}" class="block px-4 py-2 hover:bg-gray-100">📊 Resumen Sucursal</a>
            </div>
        </div>
    @endif

    <span class="text-gray-700 mt-3">👤 {{ Auth::user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="hover:text-red-600 transition">Cerrar sesión</button>
    </form>
@else
    <a href="{{ route('login') }}" class="hover:text-blue-700 transition">🔐 Login</a>
    <a href="{{ route('register') }}" class="hover:text-blue-700 transition">📝 Registro</a>
@endauth
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

        {{-- TAILWIND EXTRA CLASES PERSONALIZADAS --}}
        <style>
            .animate-slide-down {
                animation: slideDown 0.3s ease-out forwards;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .group:hover .group-hover\:flex {
                display: flex !important;
            }
        </style>
    </header>


    <main class="max-w-7xl mx-auto py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white py-10 mt-16 px-4" data-aos="fade-up">
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-10">

        {{-- LOGO + DESCRIPCIÓN --}}
        <div class="flex flex-col items-start space-y-4">
            <img src="{{ asset('storage/Logo.png') }}" alt="Latina Pizza Logo"
                class="h-14 w-auto drop-shadow-lg transition-transform duration-300 hover:scale-105">
            <p class="text-sm text-gray-400 leading-6">
                Gracias por elegir Latina Pizza. Te llevamos el mejor sabor a la puerta de tu casa.
            </p>
        </div>

        {{-- ENLACES RÁPIDOS --}}
        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">Enlaces</h4>
            <a href="/" class="block text-gray-300 hover:text-red-300 transition">Inicio</a>
            <a href="/catalogo" class="block text-gray-300 hover:text-red-300 transition">Menú</a>
            <a href="{{ route('usuario.pedidos') }}" class="block text-gray-300 hover:text-red-300 transition">Mis Pedidos</a>
            @auth
                <a href="{{ route('logout') }}" class="block text-gray-300 hover:text-red-300 transition">Cerrar sesión</a>
            @else
                <a href="{{ route('login') }}" class="block text-gray-300 hover:text-red-300 transition">Login</a>
                <a href="{{ route('register') }}" class="block text-gray-300 hover:text-red-300 transition">Registro</a>
            @endauth
        </div>

        {{-- CONTACTO + HORARIOS --}}
        <div class="space-y-3">
            <h4 class="text-red-400 font-semibold mb-2 text-lg">Contacto</h4>
            <p class="text-sm text-gray-400">📍 San José, Costa Rica</p>
            <p class="text-sm text-gray-400">📞 +506 8888-8888</p>
            <p class="text-sm text-gray-400">✉️ contacto@latinapizza.com</p>

            <h4 class="text-red-400 font-semibold mt-5 mb-1 text-lg">Horarios</h4>
            <p class="text-sm text-gray-400">Lunes a Domingo: 11:00am - 10:00pm</p>
            <p class="text-sm text-gray-500 italic">Aplica en todos los locales</p>

            <div class="flex space-x-4 mt-4">
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-gray-400 hover:text-red-400 transition duration-300"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <div class="mt-10 text-center text-gray-500 text-sm border-t border-gray-800 pt-4">
        © {{ date('Y') }} Latina Pizza. Todos los derechos reservados.
    </div>
</footer>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800, // Duración de la animación
            once: true, // Solo animar una vez
            easing: 'ease-in-out', // Efecto de suavizado
        });
    </script>
</body>
</html>
