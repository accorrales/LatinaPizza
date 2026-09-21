<section>
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600"><i class="fas fa-shield-halved"></i></span>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-red-600">Seguridad</p>
            <h2 class="mt-1 text-xl font-bold text-[#071426]">Cambiar contraseña</h2>
            <p class="mt-1 text-sm leading-6 text-slate-500">Usá una contraseña larga y única para proteger tu cuenta.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-7 space-y-5" data-show-loading>
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña actual</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                placeholder="••••••••">
            @foreach($errors->updatePassword->get('current_password') as $message)<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@endforeach
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="update_password_password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
                @foreach($errors->updatePassword->get('password') as $message)<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@endforeach
            </div>

            <div>
                <label for="update_password_password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••">
                @foreach($errors->updatePassword->get('password_confirmation') as $message)<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@endforeach
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/15 transition hover:-translate-y-0.5 hover:bg-red-700">
                Actualizar contraseña
                <i class="fas fa-lock text-xs"></i>
            </button>
            @if (session('status') === 'password-updated')
                <p x-data="flashMessage" x-show="show" x-transition class="text-sm font-medium text-emerald-600">Contraseña actualizada.</p>
            @endif
        </div>
    </form>
</section>
