<x-guest-layout>
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-red-700">
            <i class="fas fa-user-plus"></i>
            Nueva cuenta
        </span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">Creá tu cuenta</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Registrate una vez y hacé tus próximos pedidos mucho más rápido.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nombre completo</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="far fa-user"></i></span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Tu nombre">
            </div>
            @error('name')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="far fa-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="tu@email.com">
            </div>
            @error('email')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
                @error('password')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
                @error('password_confirmation')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 text-xs leading-5 text-blue-800">
            <i class="fas fa-shield-halved mr-2"></i>
            Tu cuenta se crea como cliente y tus credenciales se almacenan de forma segura.
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
            Crear mi cuenta
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">
        ¿Ya tenés una cuenta?
        <a href="{{ route('login') }}" class="font-bold text-blue-700 transition hover:text-blue-900">Iniciá sesión</a>
    </p>
</x-guest-layout>
