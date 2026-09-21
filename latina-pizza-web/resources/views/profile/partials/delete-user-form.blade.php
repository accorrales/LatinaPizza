<section class="space-y-6">
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600"><i class="far fa-trash-can"></i></span>
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-red-600">Zona de riesgo</p>
            <h2 class="mt-1 text-xl font-bold text-[#071426]">Eliminar cuenta</h2>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">Esta acción elimina permanentemente tu cuenta y sus datos asociados. No se puede deshacer.</p>
        </div>
    </header>

    <div class="rounded-2xl border border-red-100 bg-red-50/60 px-4 py-4 text-sm leading-6 text-red-800">
        <i class="fas fa-triangle-exclamation mr-2"></i>
        Antes de eliminarla, asegurate de haber guardado cualquier información de tus pedidos que querás conservar.
    </div>

    <button
        type="button"
        x-data="{}"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-600 transition hover:-translate-y-0.5 hover:bg-red-600 hover:text-white"
    >
        <i class="far fa-trash-can text-xs"></i>
        Eliminar mi cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-7">
            @csrf
            @method('delete')

            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600"><i class="fas fa-triangle-exclamation"></i></div>
            <h2 class="mt-4 text-xl font-bold text-[#071426]">¿Eliminar tu cuenta definitivamente?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Ingresá tu contraseña para confirmar. Una vez eliminada, no podremos recuperar la cuenta ni sus datos.</p>

            <div class="mt-6">
                <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input id="password" name="password" type="password" placeholder="••••••••"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100">
                @foreach($errors->userDeletion->get('password') as $message)<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@endforeach
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</button>
                <button type="submit" class="rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-red-600/15 transition hover:bg-red-700">Sí, eliminar cuenta</button>
            </div>
        </form>
    </x-modal>
</section>
