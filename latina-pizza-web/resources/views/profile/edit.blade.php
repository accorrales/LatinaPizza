@extends('layouts.app')

@section('meta_description', 'Administrá tus datos, seguridad y cuenta de Latina Pizza.')

@section('content')
<div class="w-screen max-w-[1440px] relative left-1/2 -translate-x-1/2 -mt-8 min-h-[70vh] bg-[#f7f9fc] text-slate-950">
    <div class="mx-auto max-w-[1296px] px-4 py-8 sm:px-6 sm:py-12 lg:px-8 lg:py-14">
        <header class="mb-8 sm:mb-10">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.15em] text-blue-700">
                <i class="far fa-user"></i>
                Mi cuenta
            </span>
            <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-[-0.04em] text-[#071426] sm:text-4xl lg:text-5xl">Tu perfil</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500 sm:text-base">Actualizá tus datos y mantené tu cuenta segura desde un solo lugar.</p>
                </div>
                <a href="{{ route('usuario.pedidos') }}" class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200">
                    <i class="fas fa-receipt text-xs"></i>
                    Ver mis pedidos
                </a>
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-[310px_minmax(0,1fr)] lg:items-start">
            <aside class="lg:sticky lg:top-28">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_16px_50px_rgba(7,20,38,0.07)]">
                    <div class="bg-[#071426] px-6 py-7 text-white">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold text-blue-200">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <h2 class="mt-5 text-xl font-bold">{{ $user->name }}</h2>
                        <p class="mt-1 break-all text-sm text-slate-300">{{ $user->email }}</p>
                    </div>

                    <div class="space-y-3 px-5 py-5 text-sm">
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-slate-500">Estado</span>
                            <span class="inline-flex items-center gap-2 font-semibold text-emerald-600"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Activa</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-slate-500">Correo</span>
                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail)
                                <span class="font-semibold {{ $user->hasVerifiedEmail() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->hasVerifiedEmail() ? 'Verificado' : 'Pendiente' }}</span>
                            @else
                                <span class="font-semibold text-emerald-600">Verificado</span>
                            @endif
                        </div>
                        <a href="{{ route('catalogo.index') }}" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                            <i class="fas fa-pizza-slice text-xs"></i>
                            Volver al menú
                        </a>
                    </div>
                </div>
            </aside>

            <div class="space-y-6">
                <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_14px_45px_rgba(7,20,38,0.06)] sm:p-7 lg:p-8">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_14px_45px_rgba(7,20,38,0.06)] sm:p-7 lg:p-8">
                    @include('profile.partials.update-password-form')
                </section>

                <section class="rounded-[28px] border border-red-100 bg-white p-5 shadow-[0_14px_45px_rgba(7,20,38,0.05)] sm:p-7 lg:p-8">
                    @include('profile.partials.delete-user-form')
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
