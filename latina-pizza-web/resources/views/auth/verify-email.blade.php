<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
            <i class="far fa-envelope-open text-2xl"></i>
        </div>
        <span class="mt-5 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700">Verificación</span>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.035em] text-[#071426] sm:text-4xl">Revisá tu correo</h2>
        <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">
            Te enviamos un enlace de verificación. Abrilo para activar tu cuenta y continuar usando Latina Pizza.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-circle-check mr-2"></i>
            Te enviamos un enlace nuevo al correo de tu cuenta.
        </div>
    @endif

    <div class="mt-7 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                Reenviar correo de verificación
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                Cerrar sesión
                <i class="fas fa-right-from-bracket text-xs"></i>
            </button>
        </form>
    </div>
</x-guest-layout>
