<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Latina Pizza 🍕</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <header class="bg-red-600 text-white p-4 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">Latina Pizza</h1>
            <nav>
                <a href="/" class="px-4 hover:underline">Inicio</a>
                <a href="/catalogo" class="px-4 hover:underline">Menú</a>

                @auth
                    <span class="px-4">Hola, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 hover:underline">Cerrar sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-4 hover:underline">Login</a>
                    <a href="{{ route('register') }}" class="px-4 hover:underline">Registrarse</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white text-center p-4">
        © {{ date('Y') }} Latina Pizza. Todos los derechos reservados.
    </footer>
</body>
</html>
