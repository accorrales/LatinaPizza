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
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Crear una cuenta</h2>

            <!-- Formulario -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nombre -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                    <input id="name" type="text" name="name" required autofocus
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500"
                        value="{{ old('name') }}">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                    <input id="email" type="email" name="email" required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500"
                        value="{{ old('email') }}">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar contraseña -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
                    @error('password_confirmation')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botón -->
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition shadow">
                    Registrarse
                </button>
            </form>

            <!-- Enlace a login -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">¿Ya tienes una cuenta? 
                    <a href="{{ route('login') }}" class="text-red-600 hover:underline font-semibold">
                        Inicia sesión
                    </a>
                </p>
            </div>

            <!-- Footer -->
            <p class="text-xs text-center text-gray-500 mt-6">© {{ now()->year }} Latina Pizza. Todos los derechos reservados.</p>
        </div>
    </div>
</x-guest-layout>

