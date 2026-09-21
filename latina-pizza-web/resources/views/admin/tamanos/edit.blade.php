@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div><div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i> Editar tamaño</div><h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/tamanos_admin.edit.titulo') }}</h1><p class="mt-2 text-sm text-slate-500">Los cambios de precio se reflejan en las pizzas vinculadas.</p></div>
            <a href="{{ route('admin.tamanos.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>
        @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form id="formEditarTamano" action="{{ route('admin.tamanos.update', $tamano->id) }}" method="POST" class="space-y-6" data-show-loading>
            @csrf @method('PUT')
            <div><label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/tamanos_admin.edit.nombre') }}</label><input type="text" name="nombre" id="nombre" required value="{{ old('nombre', $tamano->nombre) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"></div>
            <div><label for="precio_base" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/tamanos_admin.edit.precio_base') }}</label><div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">₡</span><input type="number" name="precio_base" id="precio_base" step="0.01" required value="{{ old('precio_base', $tamano->precio_base) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400"></div></div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.tamanos.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/tamanos_admin.edit.cancelar') }}</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/tamanos_admin.edit.actualizar') }}</button></div>
        </form>
    </section>
</div>
@endsection
