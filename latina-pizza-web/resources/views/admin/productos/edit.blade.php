@extends('layouts.app')

@section('content')
@php
    $isPizza = strtolower($producto['categoria']['nombre'] ?? '') === 'pizza';
@endphp

<div class="mx-auto max-w-6xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(280px,.55fr)]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i> Editar producto</div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/productos_admin.edit.titulo') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">Actualizá los datos comerciales sin alterar la lógica del catálogo.</p>
                </div>
                <a href="{{ route('admin.productos.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            </div>

            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('admin.productos.update', $producto['id']) }}" method="POST" class="space-y-6" data-show-loading>
                @csrf @method('PUT')
                <input type="hidden" name="categoria_id" value="{{ $producto['categoria_id'] }}">

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="nombre" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.nombre') }}</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto['nombre']) }}" @if($isPizza) readonly @endif class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400 {{ $isPizza ? 'cursor-not-allowed bg-slate-100 text-slate-500' : 'bg-slate-50' }}">
                    </div>

                    <div class="md:col-span-2">
                        <label for="descripcion" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.descripcion') }}</label>
                        <textarea name="descripcion" id="descripcion" rows="4" @if($isPizza) readonly @endif class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400 {{ $isPizza ? 'cursor-not-allowed bg-slate-100 text-slate-500' : 'bg-slate-50' }}">{{ old('descripcion', $producto['descripcion']) }}</textarea>
                    </div>

                    <div>
                        <label for="precio" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.precio') }}</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">₡</span>
                            <input type="number" name="precio" id="precio" step="0.01" value="{{ old('precio', $producto['precio']) }}" required @if($isPizza) readonly @endif class="w-full rounded-2xl border-slate-200 py-3 pl-9 pr-4 text-sm focus:border-blue-400 focus:ring-blue-400 {{ $isPizza ? 'cursor-not-allowed bg-slate-100 text-slate-500' : 'bg-slate-50' }}">
                        </div>
                        @if($isPizza)
                            <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-700">El precio de pizzas se controla desde <a href="{{ route('admin.tamanos.index') }}" class="font-bold underline">Tamaños</a>.</p>
                        @endif
                    </div>

                    <div>
                        <label for="estado" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.estado') }}</label>
                        <select name="estado" id="estado" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                            <option value="1" @selected((string) old('estado', $producto['estado'] ? '1' : '0') === '1')>{{ __('viewAdmin/productos_admin.edit.activo') }}</option>
                            <option value="0" @selected((string) old('estado', $producto['estado'] ? '1' : '0') === '0')>{{ __('viewAdmin/productos_admin.edit.inactivo') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="imagen" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.imagen') }}</label>
                        <input type="text" name="imagen" id="imagen" value="{{ old('imagen', $producto['imagen']) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.categoria') }}</label>
                        <div class="flex min-h-12 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-semibold text-slate-600"><i class="fa-solid fa-layer-group text-slate-400"></i>{{ $producto['categoria']['nombre'] }}</div>
                    </div>
                </div>

                @if($isPizza)
                    <div class="rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5 sm:p-6">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-2xl bg-blue-600 text-white"><i class="fa-solid fa-pizza-slice"></i></div>
                            <div><p class="font-black text-slate-900">Configuración de pizza</p><p class="text-xs text-slate-500">Ajustá sabor y tamaño vinculados.</p></div>
                        </div>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="sabor_id" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.sabor') }}</label>
                                <select name="sabor_id" id="sabor_id" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                                    @foreach ($sabores['data'] ?? $sabores as $sabor)<option value="{{ $sabor['id'] }}" @selected(old('sabor_id', $producto['sabor_id']) == $sabor['id'])>{{ $sabor['nombre'] }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label for="tamano_id" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/productos_admin.edit.tamano') }}</label>
                                <select name="tamano_id" id="tamano_id" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                                    @foreach ($tamanos as $tamano)<option value="{{ $tamano['id'] }}" @selected(old('tamano_id', $producto['tamano_id']) == $tamano['id'])>{{ $tamano['nombre'] }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.productos.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white shadow-lg shadow-blue-950/10 transition hover:-translate-y-0.5 hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/productos_admin.edit.guardar') }}</button>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="aspect-[4/3] bg-slate-100">
                    @if(!empty($producto['imagen']))<img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="h-full w-full object-cover">@else<div class="grid h-full place-items-center text-5xl text-slate-300"><i class="fa-solid fa-image"></i></div>@endif
                </div>
                <div class="p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Vista rápida</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900">{{ $producto['nombre'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $producto['categoria']['nombre'] }}</p>
                    <p class="mt-4 text-2xl font-black text-red-500">₡{{ number_format($producto['precio'], 0) }}</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
