@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-layer-group"></i> Categoría</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/categorias_admin.editar_categoria') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Actualizá nombre y descripción sin perder los productos asociados.</p>
            </div>
            <a href="{{ route('admin.categorias.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i>{{ __('viewAdmin/categorias_admin.volver') }}</a>
        </div>

        @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ route('admin.categorias.update', $categoria['id']) }}" class="space-y-6" data-show-loading>
            @csrf @method('PUT')
            <div>
                <label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/categorias_admin.nombre') }}</label>
                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $categoria['nombre']) }}" required class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
            </div>
            <div>
                <label for="descripcion" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/categorias_admin.descripcion') }}</label>
                <textarea name="descripcion" id="descripcion" rows="5" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">{{ old('descripcion', $categoria['descripcion'] ?? '') }}</textarea>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.categorias.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white shadow-lg shadow-blue-950/10 transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/categorias_admin.actualizar') }}</button>
            </div>
        </form>
    </section>
</div>
@endsection
