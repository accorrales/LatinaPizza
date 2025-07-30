<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border-t-4 border-red-600">
            
            <!-- Logo -->
            <div class="flex justify-center mb-6">               
                <a href="/">
                        <img src="{{ asset('images/Logo.png') }}" alt="Latina Pizza" class="h-16">
                </a>
            </div>

            <!-- Título -->
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Iniciar sesión</h2>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Formulario -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                    <input id="email" type="email" name="email" required autofocus
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500"
                        value="{{ old('email') }}">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-600">Recuérdame</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-red-600 hover:underline">¿Olvidaste tu contraseña?</a>
                    @endif
                </div>

                <!-- Botón -->
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition shadow">
                    Iniciar sesión
                </button>
                                <!-- Separador -->
                <div class="my-6 text-center border-t border-gray-200 relative">
                    <span class="absolute left-1/2 -translate-x-1/2 -top-3 bg-white px-2 text-gray-500 text-sm">¿Nuevo aquí?</span>
                </div>

                <!-- Botón de registrarse -->
                <a href="{{ route('register') }}"
                class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-red-600 font-semibold py-2 px-4 rounded-md transition shadow border border-red-300">
                    Crear una cuenta
                </a>

            </form>

            <!-- Footer -->
            <p class="text-xs text-center text-gray-500 mt-6">© {{ now()->year }} Latina Pizza. Todos los derechos reservados.</p>
        </div>
    </div>
</x-guest-layout>
