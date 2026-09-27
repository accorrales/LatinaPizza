<x-guest-layout>
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700">
            <i class="fas fa-key"></i>
            Recuperar acceso
        </span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">¿Olvidaste tu contraseña?</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Paso 1 de 2. Ingresá tu correo para recibir un código de seis dígitos. Tendrás 10 minutos para usarlo.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-circle-check mr-2"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" data-recovery-form>
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="far fa-envelope"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" maxlength="255" aria-describedby="email-error" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="tu@email.com">
            </div>
            <p id="email-error" role="alert" class="mt-2 text-xs font-medium text-red-600">{{ $errors->first('email') }}</p>
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
            Enviar código de recuperación
            <i class="fas fa-paper-plane text-xs"></i>
        </button>
    </form>

    <a href="{{ route('login') }}" class="mt-7 inline-flex w-full items-center justify-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Volver a iniciar sesión
    </a>
</x-guest-layout>
