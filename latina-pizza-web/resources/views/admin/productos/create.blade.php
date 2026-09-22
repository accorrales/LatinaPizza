@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(280px,.55fr)]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-plus"></i> Nuevo producto</div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/productos_admin.create.titulo') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Configurá la información comercial del producto. Los campos de pizza aparecen automáticamente cuando elegís esa categoría.</p>
                </div>
                <a href="{{ route('admin.productos.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('admin.productos.store') }}" method="POST" class="space-y-6" data-show-loading>
                @csrf
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.nombre') }}</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-blue-400 focus:ring-blue-400">
                    </div>
                    <div class="md:col-span-2">
                        <label for="descripcion" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.descripcion') }}</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-blue-400 focus:ring-blue-400">{{ old('descripcion') }}</textarea>
                    </div>
                    <div>
                        <label for="precio" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.precio') }}</label>
                        <div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">₡</span><input type="number" name="precio" id="precio" value="{{ old('precio') }}" step="0.01" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400"></div>
                        <p class="mt-2 text-xs text-slate-400">Para pizzas, el precio se toma automáticamente del tamaño.</p>
                    </div>
                    <div>
                        <label for="estado" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.estado') }}</label>
                        <select name="estado" id="estado" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"><option value="1" @selected(old('estado', '1') === '1')>{{ __('viewAdmin/productos_admin.create.activo') }}</option><option value="0" @selected(old('estado') === '0')>{{ __('viewAdmin/productos_admin.create.inactivo') }}</option></select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="imagen" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.imagen') }}</label>
                        <input type="text" name="imagen" id="imagen" value="{{ old('imagen') }}" placeholder="https://..." class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                    </div>
                    <div class="md:col-span-2">
                        <label for="categoria_id" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.categoria') }}</label>
                        <select name="categoria_id" id="categoria_id" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400" required><option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>@foreach ($categorias['data'] ?? $categorias as $categoria)<option value="{{ $categoria['id'] }}" @selected((string) old('categoria_id') === (string) $categoria['id'])>{{ $categoria['nombre'] }}</option>@endforeach</select>
                    </div>
                </div>

                <input type="hidden" name="categoria_nombre" id="categoria_nombre">

                <div id="camposPizza" class="hidden rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5 sm:p-6">
                    <div class="mb-5 flex items-center gap-3"><div class="grid h-10 w-10 place-items-center rounded-2xl bg-blue-600 text-white"><i class="fa-solid fa-pizza-slice"></i></div><div><p class="font-black text-slate-900">Configuración de pizza</p><p class="text-xs text-slate-500">Asigná sabor y tamaño al producto.</p></div></div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div><label for="sabor_id" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.sabor') }}</label><select name="sabor_id" id="sabor_id" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"><option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>@foreach ($sabores['data'] ?? $sabores as $sabor)<option value="{{ $sabor['id'] }}" @selected((string) old('sabor_id') === (string) $sabor['id'])>{{ $sabor['nombre'] }}</option>@endforeach</select></div>
                        <div><label for="tamano_id" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.create.tamano') }}</label><select name="tamano_id" id="tamano_id" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400"><option value="">{{ __('viewAdmin/productos_admin.create.seleccionar') }}</option>@foreach ($tamanos as $tamano)<option value="{{ $tamano['id'] }}" @selected((string) old('tamano_id') === (string) $tamano['id'])>{{ $tamano['nombre'] }}</option>@endforeach</select></div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.productos.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-7 text-sm font-bold text-white shadow-lg shadow-red-950/10 transition hover:-translate-y-0.5 hover:bg-red-600"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/productos_admin.create.guardar') }}</button></div>
            </form>
        </section>

        <aside class="space-y-4"><div class="rounded-[2rem] bg-[#071426] p-6 text-white shadow-xl shadow-slate-950/10"><div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/10 text-blue-300"><i class="fa-solid fa-lightbulb"></i></div><h2 class="mt-5 text-xl font-black">Tip de catálogo</h2><p class="mt-2 text-sm leading-6 text-slate-300">Usá nombres cortos, una descripción clara y una imagen consistente. El precio de pizzas depende del tamaño configurado.</p></div></aside>
    </div>
</div>
@endsection
