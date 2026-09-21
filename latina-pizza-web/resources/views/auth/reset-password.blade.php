<x-guest-layout>
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700">
            <i class="fas fa-shield-keyhole"></i>
            Nueva contraseña
        </span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">Recuperá tu cuenta</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Creá una contraseña nueva para volver a entrar a Latina Pizza.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
            @error('email')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                placeholder="••••••••">
            @error('password')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                placeholder="••••••••">
            @error('password_confirmation')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
            Guardar nueva contraseña
            <i class="fas fa-check text-xs"></i>
        </button>
    </form>
</x-guest-layout>
