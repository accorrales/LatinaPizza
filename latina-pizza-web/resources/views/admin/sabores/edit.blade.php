@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i> Editar sabor</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/sabores_admin.edit.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Actualizá el contenido que ve el cliente en el catálogo.</p>
            </div>
            <a href="{{ route('admin.sabores.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>

        @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form id="formEditarSabor" action="{{ route('admin.sabores.update', $sabor->id) }}" method="POST" class="space-y-6" data-show-loading>
            @csrf @method('PUT')
            <div>
                <label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.edit.nombre') }}</label>
                <input type="text" name="nombre" id="nombre" required value="{{ old('nombre', $sabor->nombre) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
            </div>
            <div>
                <label for="descripcion" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.edit.descripcion') }}</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">{{ old('descripcion', $sabor->descripcion) }}</textarea>
            </div>
            <div>
                <label for="imagen" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.edit.imagen') }}</label>
                <input type="url" name="imagen" id="imagen" value="{{ old('imagen', $sabor->imagen) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
            </div>
            @if($sabor->imagen)
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 p-3"><img src="{{ $sabor->imagen }}" alt="{{ $sabor->nombre }}" class="max-h-64 w-full rounded-2xl object-cover"></div>
            @endif
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.sabores.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/sabores_admin.edit.cancelar') }}</a>
                <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/sabores_admin.edit.actualizar') }}</button>
            </div>
        </form>
    </section>
</div>
@endsection
