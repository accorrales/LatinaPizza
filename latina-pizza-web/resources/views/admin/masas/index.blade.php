@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-circle-dot"></i> Personalización</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/masas_admin.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Controlá las masas disponibles y su recargo sobre el precio base.</p>
            </div>
            <a href="{{ route('admin.masas.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/masas_admin.nuevo') }}</a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($masas as $masa)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-950/5">
                        <div class="flex items-start justify-between"><div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-amber-600"><i class="fa-solid fa-circle-dot"></i></div><span class="text-xs font-bold text-slate-400">#{{ $masa['id'] }}</span></div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">{{ $masa['tipo'] }}</h3>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('viewAdmin/masas_admin.precio_extra') }}</p>
                        <p class="mt-2 text-3xl font-black text-red-500">₡{{ number_format($masa['precio_extra'], 0, ',', '.') }}</p>
                        <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.masas.edit', $masa['id']) }}" data-show-loading class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/masas_admin.editar') }}</a>
                            <form action="{{ route('admin.masas.destroy', $masa['id']) }}" method="POST" class="flex-1" data-show-loading data-confirm="{{ __('viewAdmin/masas_admin.confirmar_eliminar') }}">@csrf @method('DELETE')<button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/masas_admin.eliminar') }}</button></form>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 py-14 text-center text-sm text-slate-400">{{ __('viewAdmin/masas_admin.sin_registros') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
