@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600"><i class="fa-solid fa-pizza-slice"></i> Nuevo sabor</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/sabores_admin.create.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Definí nombre, descripción e imagen para mostrarlo correctamente en el menú.</p>
            </div>
            <a href="{{ route('admin.sabores.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>

        @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form id="formCrearSabor" action="{{ route('admin.sabores.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data" data-show-loading>
            @csrf
            <div>
                <label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.create.nombre') }}</label>
                <input type="text" name="nombre" id="nombre" required value="{{ old('nombre') }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
            </div>
            <div>
                <label for="descripcion" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.create.descripcion') }}</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">{{ old('descripcion') }}</textarea>
            </div>
            <div>
                <label for="imagen" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/sabores_admin.create.imagen') }}</label>
                <input type="url" name="imagen" id="imagen" placeholder="https://cdn.example.com/pizzas/sabor.jpg" value="{{ old('imagen') }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                <p class="mt-2 text-xs text-slate-400">Usá una URL HTTPS con imagen horizontal y buena resolución.</p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.sabores.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/sabores_admin.create.cancelar') }}</a>
                <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-7 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/sabores_admin.create.guardar') }}</button>
            </div>
        </form>
    </section>
</div>
@endsection
