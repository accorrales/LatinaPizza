@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-1 sm:px-2" data-promotion-editor data-next-index="{{ count($promocion['componentes']) }}">
    @include('admin.partials.catalog-nav')

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,.6fr)]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div><div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i> Editar promoción</div><h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/promociones_admin.edit.titulo') }}</h1><p class="mt-2 text-sm text-slate-500">Actualizá precio, contenido y composición del combo.</p></div>
                <a href="{{ route('admin.promociones.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
            </div>

            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <form action="{{ route('admin.promociones.update', $promocion['id']) }}" method="POST" data-show-loading class="space-y-7">
                @csrf @method('PUT')
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/promociones_admin.edit.nombre') }}</label><input type="text" name="nombre" value="{{ old('nombre', $promocion['nombre']) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400" required></div>
                    <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/promociones_admin.edit.descripcion') }}</label><textarea name="descripcion" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">{{ old('descripcion', $promocion['descripcion']) }}</textarea></div>
                    <div><label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/promociones_admin.edit.precio_total') }}</label><div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">₡</span><input type="number" name="precio_total" step="0.01" value="{{ old('precio_total', $promocion['precio_total']) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400" required></div></div>
                    <div><label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/promociones_admin.edit.precio_sugerido') }}</label><div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">₡</span><input type="number" name="precio_sugerido" step="0.01" value="{{ old('precio_sugerido', $promocion['precio_sugerido']) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400"></div></div>
                    <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/promociones_admin.edit.imagen') }}</label><input type="text" name="imagen" value="{{ old('imagen', $promocion['imagen']) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"></div>
                </div>

                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5">
                    <div><p class="font-black text-slate-900">{{ __('viewAdmin/promociones_admin.edit.incluye_bebida') }}</p><p class="mt-1 text-xs text-slate-500">Indica si el combo incluye al menos una bebida.</p></div>
                    <input type="checkbox" name="incluye_bebida" value="1" {{ old('incluye_bebida', $promocion['incluye_bebida']) ? 'checked' : '' }} class="h-5 w-5 rounded border-blue-200 text-blue-600 focus:ring-blue-500">
                </label>

                <div class="border-t border-slate-100 pt-7">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Constructor</p><h2 class="mt-1 text-xl font-black text-slate-900">{{ __('viewAdmin/promociones_admin.edit.componentes_titulo') }}</h2></div><button type="button" id="agregar-componente" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-blue-600 px-5 text-xs font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-plus"></i>Agregar componente</button></div>

                    <div id="componentes" class="space-y-4">
                        @foreach ($promocion['componentes'] as $i => $componente)
                            <div class="componente rounded-3xl border border-slate-200 bg-slate-50 p-5" data-component>
                                <div class="mb-4 flex items-center justify-between gap-4"><div class="flex items-center gap-3"><div class="grid h-9 w-9 place-items-center rounded-2xl bg-white text-blue-600 shadow-sm"><i class="fa-solid fa-puzzle-piece"></i></div><span class="text-sm font-black text-slate-900">Componente {{ $i + 1 }}</span></div><button type="button" class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600" data-remove><i class="fa-solid fa-trash mr-1"></i>Eliminar</button></div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.tipo') }}</label><select name="componentes[{{ $i }}][tipo]" class="component-type w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm" required><option value="pizza" {{ $componente['tipo'] == 'pizza' ? 'selected' : '' }}>{{ __('viewAdmin/promociones_admin.edit.tipo_pizza') }}</option><option value="bebida" {{ $componente['tipo'] == 'bebida' ? 'selected' : '' }}>{{ __('viewAdmin/promociones_admin.edit.tipo_bebida') }}</option></select></div>
                                    <div><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.cantidad') }}</label><input type="number" name="componentes[{{ $i }}][cantidad]" value="{{ $componente['cantidad'] }}" min="1" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm" required></div>
                                </div>
                                <div data-pizza-fields @class(['mt-4', 'hidden' => $componente['tipo'] === 'bebida'])><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.tamano') }}</label><select name="componentes[{{ $i }}][tamano_id]" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm"><option value="">{{ __('viewAdmin/promociones_admin.edit.seleccionar_tamano') }}</option>@foreach ($tamanos as $tamano)<option value="{{ $tamano['id'] }}" {{ ($componente['tamano_id'] ?? null) == $tamano['id'] ? 'selected' : '' }}>{{ $tamano['nombre'] }}</option>@endforeach</select></div>
                                <div data-drink-fields @class(['mt-4', 'hidden' => $componente['tipo'] !== 'bebida'])><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Bebida</label><select name="componentes[{{ $i }}][producto_id]" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm"><option value="">Seleccione una bebida</option>@foreach ($bebidas as $bebida)<option value="{{ $bebida['id'] }}" {{ ($componente['producto_id'] ?? null) == $bebida['id'] ? 'selected' : '' }}>{{ $bebida['nombre'] }}</option>@endforeach</select></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.promociones.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">{{ __('viewAdmin/promociones_admin.edit.cancelar') }}</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/promociones_admin.edit.actualizar') }}</button></div>
            </form>

            <template id="promotion-component-template">
                <div class="componente rounded-3xl border border-slate-200 bg-slate-50 p-5" data-component>
                    <div class="mb-4 flex items-center justify-between gap-4"><div class="flex items-center gap-3"><div class="grid h-9 w-9 place-items-center rounded-2xl bg-white text-blue-600 shadow-sm"><i class="fa-solid fa-puzzle-piece"></i></div><span class="text-sm font-black text-slate-900">Componente</span></div><button type="button" class="rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600" data-remove><i class="fa-solid fa-trash mr-1"></i>Eliminar</button></div>
                    <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.tipo') }}</label><select name="componentes[__INDEX__][tipo]" class="component-type w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm" required><option value="pizza">{{ __('viewAdmin/promociones_admin.edit.tipo_pizza') }}</option><option value="bebida">{{ __('viewAdmin/promociones_admin.edit.tipo_bebida') }}</option></select></div><div><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.cantidad') }}</label><input type="number" name="componentes[__INDEX__][cantidad]" value="1" min="1" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm" required></div></div>
                    <div data-pizza-fields class="mt-4"><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ __('viewAdmin/promociones_admin.edit.tamano') }}</label><select name="componentes[__INDEX__][tamano_id]" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm"><option value="">{{ __('viewAdmin/promociones_admin.edit.seleccionar_tamano') }}</option>@foreach($tamanos as $tamano)<option value="{{ $tamano['id'] }}">{{ $tamano['nombre'] }}</option>@endforeach</select></div>
                    <div data-drink-fields class="mt-4 hidden"><label class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Bebida</label><select name="componentes[__INDEX__][producto_id]" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm"><option value="">Seleccione una bebida</option>@foreach($bebidas as $bebida)<option value="{{ $bebida['id'] }}">{{ $bebida['nombre'] }}</option>@endforeach</select></div>
                </div>
            </template>
        </section>

        <aside class="space-y-4">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="aspect-[16/10] bg-gradient-to-br from-[#071426] to-blue-700">@if(!empty($promocion['imagen']))<img src="{{ $promocion['imagen'] }}" alt="{{ $promocion['nombre'] }}" class="h-full w-full object-cover opacity-85">@else<div class="grid h-full place-items-center text-5xl text-white/20"><i class="fa-solid fa-tags"></i></div>@endif</div>
                <div class="p-5"><p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Vista rápida</p><h2 class="mt-2 text-xl font-black text-slate-900">{{ $promocion['nombre'] }}</h2><p class="mt-4 text-3xl font-black text-red-500">₡{{ number_format($promocion['precio_total'], 0) }}</p><p class="mt-2 text-xs text-slate-400">{{ count($promocion['componentes']) }} componentes configurados</p></div>
            </div>
        </aside>
    </div>
</div>
@endsection
