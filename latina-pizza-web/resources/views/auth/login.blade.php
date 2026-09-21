<x-guest-layout>
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700">
            <i class="fas fa-user-lock"></i>
            Bienvenido de vuelta
        </span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">Iniciar sesión</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Entrá a tu cuenta para continuar con tu pedido, revisar historial y administrar tus datos.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-circle-check mr-2"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="far fa-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="tu@email.com">
            </div>
            @error('email')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-4">
                <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-700 transition hover:text-blue-900">¿La olvidaste?</a>
                @endif
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-key"></i></span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
            </div>
            @error('password')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex cursor-pointer items-center gap-3 text-sm text-slate-500">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>Recordarme en este dispositivo</span>
        </label>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
            Entrar a mi cuenta
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </form>

    <div class="my-7 flex items-center gap-4 text-xs font-medium text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>
        ¿Primera vez aquí?
        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-blue-700 transition hover:border-blue-200 hover:bg-blue-50">
        Crear una cuenta
        <i class="fas fa-user-plus text-xs"></i>
    </a>
</x-guest-layout>
