@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div><div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-circle-dot"></i> Nueva masa</div><h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/masas_admin.crear_titulo') }}</h1><p class="mt-2 text-sm text-slate-500">Configurá el tipo de masa y cualquier recargo adicional.</p></div>
            <a href="{{ route('admin.masas.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>
        @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form id="formCrearMasa" action="{{ route('admin.masas.store') }}" method="POST" class="space-y-6" data-show-loading>
            @csrf
            <div><label for="tipo" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/masas_admin.tipo_label') }}</label><input type="text" name="tipo" id="tipo" required value="{{ old('tipo') }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"></div>
            <div><label for="precio_extra" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/masas_admin.precio_extra_label') }}</label><div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">₡</span><input type="number" name="precio_extra" id="precio_extra" step="0.01" min="0" value="{{ old('precio_extra', 0) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400"></div></div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.masas.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/masas_admin.cancelar') }}</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/masas_admin.boton_crear') }}</button></div>
        </form>
    </section>
</div>
@endsection
