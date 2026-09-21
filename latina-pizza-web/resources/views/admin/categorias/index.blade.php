@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-layer-group"></i> Organización</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/categorias_admin.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Agrupá el catálogo, controlá visibilidad y mantené descripciones claras para el cliente.</p>
            </div>
            <form method="GET" action="{{ route('admin.categorias.index') }}" class="flex w-full max-w-lg gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('viewAdmin/categorias_admin.buscar') }}..." class="min-w-0 flex-1 rounded-full border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-400 focus:ring-blue-400">
                <button class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#071426] text-white" aria-label="{{ __('viewAdmin/categorias_admin.buscar') }}"><i class="fa-solid fa-magnifying-glass text-xs"></i></button>
                @if(request('q'))<a href="{{ route('admin.categorias.index') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500" aria-label="{{ __('viewAdmin/categorias_admin.limpiar') }}"><i class="fa-solid fa-xmark"></i></a>@endif
            </form>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="mb-7 rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5 sm:p-6">
                <div class="mb-5 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-blue-600 text-white"><i class="fa-solid fa-plus"></i></div>
                    <div><h2 class="font-black text-slate-900">{{ __('viewAdmin/categorias_admin.nueva_categoria') }}</h2><p class="text-xs text-slate-500">Creala sin salir del listado.</p></div>
                </div>
                <form action="{{ route('admin.categorias.store') }}" method="POST" class="grid gap-3 lg:grid-cols-[1fr_1.4fr_180px_auto]" data-show-loading>
                    @csrf
                    <input name="nombre" value="{{ old('nombre') }}" required placeholder="{{ __('viewAdmin/categorias_admin.nombre') }}" class="rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                    <input name="descripcion" value="{{ old('descripcion') }}" placeholder="{{ __('viewAdmin/categorias_admin.descripcion') }}" class="rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                    <select name="estado" class="rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                        <option value="1">{{ __('viewAdmin/categorias_admin.activa') }}</option>
                        <option value="0">{{ __('viewAdmin/categorias_admin.inactiva') }}</option>
                    </select>
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-5 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/categorias_admin.crear') }}</button>
                </form>
                @error('nombre')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($categorias as $cat)
                    <article class="group rounded-3xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-950/5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-50 text-blue-600"><i class="fa-solid fa-layer-group"></i></div>
                            @if(($cat['estado'] ?? 1) == 1)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">{{ __('viewAdmin/categorias_admin.activa') }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-500">{{ __('viewAdmin/categorias_admin.inactiva') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">{{ $cat['nombre'] }}</h3>
                        <p class="mt-2 min-h-10 text-sm leading-5 text-slate-500">{{ $cat['descripcion'] ?? 'Sin descripción' }}</p>
                        <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.categorias.edit', $cat['id']) }}" data-show-loading class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/categorias_admin.editar') }}</a>
                            <form action="{{ route('admin.categorias.destroy', $cat['id']) }}" method="POST" class="flex-1" data-confirm="{{ __('viewAdmin/categorias_admin.confirmar_eliminacion') }}" data-show-loading>
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/categorias_admin.eliminar') }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 py-14 text-center text-sm text-slate-400">{{ __('viewAdmin/categorias_admin.sin_registros') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
