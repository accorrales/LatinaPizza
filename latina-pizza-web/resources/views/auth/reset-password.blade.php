<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Paso 2 de 2 · Recuperar acceso</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-[#071426]">Revisá tu correo</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Ingresá el código de seis dígitos y elegí tu nueva contraseña. El código vence en 10 minutos y permite hasta cinco intentos.</p>
    </div>

    @if (session('status'))
        <div role="status" class="mb-5 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5" data-recovery-form>
        @csrf
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ session('recovery_email') }}" required readonly autocomplete="username"
                aria-describedby="email-error" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <p id="email-error" role="alert" class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
        </div>

        <div>
            <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">Código de recuperación</label>
            <input id="code" type="text" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6"
                aria-describedby="code-help code-error" aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-2xl tracking-[0.3em] focus:border-blue-400 focus:ring-blue-100">
            <p id="code-help" class="mt-2 text-xs text-slate-500">Podés pegar el código completo. Si pediste otro, usá el más reciente.</p>
            <p id="code-error" role="alert" class="mt-2 text-sm text-red-600">{{ $errors->first('code') }}</p>
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
            <input id="password" type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password"
                aria-describedby="password-help password-error" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-100">
            <p id="password-help" class="mt-2 text-xs text-slate-500">Usá entre 12 y 72 caracteres. Una frase larga y única es fácil de recordar.</p>
            <p id="password-error" role="alert" class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" maxlength="72" autocomplete="new-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-100">
            <button type="button" data-toggle-password aria-pressed="false" class="mt-2 text-sm font-semibold text-blue-700">Mostrar contraseñas</button>
        </div>

        <p class="text-xs leading-5 text-slate-500">Al guardar, se cerrarán las sesiones anteriores. Luego podrás iniciar sesión con tu nueva contraseña.</p>
        <button type="submit" class="w-full rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-red-700 disabled:opacity-60 focus:ring-4 focus:ring-red-100">
            Guardar nueva contraseña
        </button>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-5">
        <p class="text-sm text-slate-500">¿No llegó? Revisá spam o solicitá otro código.</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-3">
            @csrf
            <input type="hidden" name="email" value="{{ session('recovery_email') }}">
            <button type="submit" data-resend-at="{{ session('recovery_resend_at', 0) }}" class="text-sm font-bold text-blue-700 disabled:text-slate-400">Reenviar código</button>
        </form>
        <div class="mt-5 flex flex-wrap justify-between gap-3 text-sm font-semibold text-slate-600">
            <a href="{{ route('password.request') }}" class="hover:text-blue-700">Cambiar correo</a>
            <a href="{{ route('login') }}" class="hover:text-blue-700">Volver a iniciar sesión</a>
        </div>
    </div>
</x-guest-layout>
