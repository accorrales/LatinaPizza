@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex items-start justify-between gap-4">
            <div><div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i> Editar extra</div><h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/extras_admin.titulo_editar') }}</h1><p class="mt-2 text-sm text-slate-500">Actualizá el extra y sus precios por tamaño.</p></div>
            <a href="{{ route('admin.extras.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>
        @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

        <form id="formEditarExtra" method="POST" action="{{ route('admin.extras.update', $extra->id) }}" class="space-y-6" data-show-loading>
            @csrf @method('PUT')
            <div><label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/extras_admin.nombre') }} <span class="text-red-500">{{ __('viewAdmin/extras_admin.requerido') }}</span></label><input type="text" name="nombre" id="nombre" value="{{ old('nombre', $extra->nombre) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400 @error('nombre') border-red-400 @enderror" required>@error('nombre')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror</div>

            <div class="rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5 sm:p-6">
                <div class="mb-5"><p class="font-black text-slate-900">Precios por tamaño</p><p class="mt-1 text-xs text-slate-500">Estos valores alimentan el cálculo del Pizza Builder.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach(['precio_pequena', 'precio_mediana', 'precio_grande', 'precio_extragrande'] as $campo)
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/extras_admin.'.$campo) }}</label>
                            <div class="relative"><span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">₡</span><input type="number" step="0.01" name="{{ $campo }}" value="{{ old($campo, $extra->{$campo}) }}" class="w-full rounded-2xl border-blue-100 bg-white py-3 pl-8 pr-3 text-sm focus:border-blue-400 focus:ring-blue-400 @error($campo) border-red-400 @enderror"></div>
                            @error($campo)<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.extras.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/extras_admin.cancelar') }}</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/extras_admin.actualizar') }}</button></div>
        </form>
    </section>
</div>
@endsection
