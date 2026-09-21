<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Accedé a tu cuenta de Latina Pizza para ordenar, revisar pedidos y administrar tus datos.">

    <title>{{ config('app.name', 'Latina Pizza') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-950 bg-[#f7f9fc]">
    <main class="min-h-screen lg:grid lg:grid-cols-[minmax(420px,0.92fr)_minmax(520px,1.08fr)]">
        <section class="relative hidden lg:flex overflow-hidden bg-[#071426] px-14 py-12 text-white xl:px-20" aria-label="Latina Pizza">
            <div class="absolute -left-24 top-1/3 h-80 w-80 rounded-full bg-red-500/15 blur-3xl"></div>
            <div class="absolute -right-28 -top-20 h-[420px] w-[420px] rounded-full bg-blue-500/30 blur-3xl"></div>
            <div class="absolute bottom-[-160px] right-10 h-[420px] w-[420px] rounded-full bg-blue-700/20 blur-3xl"></div>

            <div class="relative z-10 flex w-full flex-col justify-between">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" aria-label="Volver a Latina Pizza">
                        <img src="{{ asset('images/Logo.png') }}" alt="Latina Pizza" class="h-16 w-auto rounded-2xl bg-white/95 p-2 shadow-2xl shadow-black/20">
                    </a>
                </div>

                <div class="max-w-xl py-12">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-100">
                        <i class="fas fa-pizza-slice text-red-400"></i>
                        Tu cuenta Latina
                    </span>
                    <h1 class="mt-6 text-5xl font-bold leading-[1.03] tracking-[-0.045em] xl:text-6xl">
                        Tu pizza favorita,<br>
                        <span class="text-blue-300">a un par de clicks.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-base leading-8 text-slate-300">
                        Guardá tus datos, revisá tus pedidos y volvé a ordenar más rápido desde una experiencia segura y simple.
                    </p>

                    <div class="mt-9 grid max-w-lg grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4 backdrop-blur">
                            <i class="fas fa-bolt text-red-400"></i>
                            <p class="mt-3 text-sm font-semibold">Más rápido</p>
                            <p class="mt-1 text-xs text-slate-400">Tus datos listos.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4 backdrop-blur">
                            <i class="fas fa-shield-halved text-blue-300"></i>
                            <p class="mt-3 text-sm font-semibold">Seguro</p>
                            <p class="mt-1 text-xs text-slate-400">Sesión protegida.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4 backdrop-blur">
                            <i class="fas fa-clock-rotate-left text-white"></i>
                            <p class="mt-3 text-sm font-semibold">Tu historial</p>
                            <p class="mt-1 text-xs text-slate-400">Pedidos a mano.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-6 text-xs text-slate-400">
                    <span>© {{ now()->year }} Latina Pizza</span>
                    <span class="inline-flex items-center gap-2"><i class="fas fa-lock text-blue-300"></i> Conexión segura</span>
                </div>
            </div>
        </section>

        <section class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-8 lg:px-12 xl:px-20">
            <div class="absolute inset-x-0 top-0 h-44 bg-gradient-to-b from-blue-50 to-transparent lg:hidden"></div>

            <div class="relative z-10 w-full max-w-[520px]">
                <div class="mb-7 flex items-center justify-between lg:hidden">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <img src="{{ asset('images/Logo.png') }}" alt="Latina Pizza" class="h-14 w-auto">
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-blue-200 hover:text-blue-700" aria-label="Volver al inicio">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>

                <div class="rounded-[32px] border border-slate-200/80 bg-white p-6 shadow-[0_24px_80px_rgba(7,20,38,0.10)] sm:p-8 lg:p-10">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs leading-5 text-slate-400">
                    Al continuar aceptás el uso seguro de tu cuenta para gestionar pedidos en Latina Pizza.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
