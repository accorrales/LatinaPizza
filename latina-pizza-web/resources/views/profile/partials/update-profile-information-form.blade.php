<section>
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><i class="far fa-id-card"></i></span>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-600">Datos personales</p>
            <h2 class="mt-1 text-xl font-bold text-[#071426]">Información de perfil</h2>
            <p class="mt-1 text-sm leading-6 text-slate-500">Actualizá tu nombre y correo electrónico.</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" data-show-loading>
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-7 space-y-5" data-show-loading>
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nombre</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
            @error('name')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
            @error('email')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p><i class="fas fa-triangle-exclamation mr-2"></i>Tu correo todavía no está verificado.</p>
                    <button form="send-verification" class="mt-2 font-bold underline underline-offset-2">Reenviar correo de verificación</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-emerald-600">Te enviamos un nuevo enlace de verificación.</p>
                @endif
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/15 transition hover:-translate-y-0.5 hover:bg-blue-700">
                Guardar cambios
                <i class="fas fa-check text-xs"></i>
            </button>
            @if (session('status') === 'profile-updated')
                <p x-data="flashMessage" x-show="show" x-transition class="text-sm font-medium text-emerald-600">Cambios guardados.</p>
            @endif
        </div>
    </form>
</section>
