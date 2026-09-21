@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-br from-[#071426] via-[#0B2344] to-red-700 px-6 py-7 text-white sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-red-100"><i class="fa-solid fa-tags"></i> Campañas</div>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ __('viewAdmin/promociones_admin.index.titulo') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-200">Armá combos con pizzas y bebidas, controlá el precio final y mantené las promos listas para el Home y catálogo.</p>
            </div>
            <a href="{{ route('admin.promociones.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white shadow-xl shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/promociones_admin.index.crear') }}</a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse($promociones as $promo)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-xl hover:shadow-red-950/5">
                        <div class="relative aspect-[16/8] overflow-hidden bg-gradient-to-br from-[#071426] to-blue-700">
                            @if(!empty($promo['imagen']))
                                <img src="{{ $promo['imagen'] }}" alt="{{ $promo['nombre'] }}" class="h-full w-full object-cover opacity-80">
                            @else
                                <div class="absolute inset-0 grid place-items-center text-5xl text-white/20"><i class="fa-solid fa-tags"></i></div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#071426]/90 to-transparent p-5 pt-12"><h3 class="text-xl font-black text-white">{{ $promo['nombre'] }}</h3></div>
                        </div>
                        <div class="p-5">
                            <p class="min-h-10 text-sm leading-5 text-slate-500">{{ $promo['descripcion'] }}</p>
                            <div class="mt-5 flex items-end justify-between gap-4">
                                <div><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('viewAdmin/promociones_admin.index.precio_total') }}</p><p class="mt-1 text-2xl font-black text-red-500">₡{{ number_format($promo['precio_total'], 0) }}</p></div>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700">Promo</span>
                            </div>
                            <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                                <a href="{{ route('admin.promociones.edit', $promo['id']) }}" data-show-loading class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/promociones_admin.index.editar') }}</a>
                                <form action="{{ route('admin.promociones.destroy', $promo['id']) }}" method="POST" class="flex-1" data-confirm="{{ __('viewAdmin/promociones_admin.index.confirmar_eliminar') }}" data-show-loading>@csrf @method('DELETE')<button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/promociones_admin.index.eliminar') }}</button></form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 py-14 text-center text-sm text-slate-400">{{ __('viewAdmin/promociones_admin.index.vacio') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
