<x-guest-layout>
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-red-700">
            <i class="fas fa-lock"></i>
            Área protegida
        </span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">Confirmá tu contraseña</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Por seguridad necesitamos confirmar que sos vos antes de continuar con esta acción.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña actual</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fas fa-key"></i></span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
            </div>
            @error('password')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
            Confirmar y continuar
            <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </form>
</x-guest-layout>
