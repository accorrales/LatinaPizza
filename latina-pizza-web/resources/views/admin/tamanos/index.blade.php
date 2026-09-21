@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Pricing base</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/tamanos_admin.index.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Estos precios funcionan como base para las pizzas y se reflejan en el catálogo.</p>
            </div>
            <a href="{{ route('admin.tamanos.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/tamanos_admin.index.nuevo') }}</a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($tamanos as $tamano)
                    <article class="rounded-3xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-950/5">
                        <div class="flex items-start justify-between gap-3"><div class="grid h-11 w-11 place-items-center rounded-2xl bg-blue-50 text-blue-600"><i class="fa-solid fa-ruler-combined"></i></div><span class="text-xs font-bold text-slate-400">#{{ $tamano['id'] }}</span></div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">{{ $tamano['nombre'] }}</h3>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('viewAdmin/tamanos_admin.index.precio_base') }}</p>
                        <p class="mt-2 text-3xl font-black text-red-500">₡{{ number_format($tamano['precio_base'], 0, ',', '.') }}</p>
                        <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.tamanos.edit', $tamano['id']) }}" data-show-loading class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/tamanos_admin.index.editar') }}</a>
                            <form action="{{ route('admin.tamanos.destroy', $tamano['id']) }}" method="POST" class="flex-1" data-show-loading data-confirm="{{ __('viewAdmin/tamanos_admin.index.confirmar_eliminar') }}">@csrf @method('DELETE')<button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/tamanos_admin.index.eliminar') }}</button></form>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 py-14 text-center text-sm text-slate-400">{{ __('viewAdmin/tamanos_admin.index.vacio') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
