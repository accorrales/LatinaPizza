@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-slate-100 bg-gradient-to-br from-[#071426] via-[#0B2344] to-blue-700 px-6 py-7 text-white sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-blue-100">
                    <i class="fa-solid fa-box-open"></i>
                    Catálogo
                </div>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ __('viewAdmin/productos_admin.index.titulo') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100/80">Administrá productos, precio, categoría y disponibilidad desde un solo lugar.</p>
            </div>
            <a href="{{ route('admin.productos.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white shadow-xl shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-red-600">
                <i class="fas fa-plus"></i>
                {{ __('viewAdmin/productos_admin.index.nuevo') }}
            </a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
            @endif

            <div class="mb-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Total</p>
                    <p class="mt-1 text-2xl font-black text-slate-900">{{ count($productos) }}</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-500">Activos</p>
                    <p class="mt-1 text-2xl font-black text-blue-700">{{ collect($productos)->where('estado', true)->count() }}</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-400">Inactivos</p>
                    <p class="mt-1 text-2xl font-black text-red-600">{{ collect($productos)->where('estado', false)->count() }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="px-5 py-4">{{ __('viewAdmin/productos_admin.index.nombre') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/productos_admin.index.categoria') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/productos_admin.index.precio') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/productos_admin.index.estado') }}</th>
                                <th class="px-5 py-4 text-right">{{ __('viewAdmin/productos_admin.index.acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($productos as $producto)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-[#F6F9FF] text-blue-600">
                                                @if(!empty($producto['imagen']))
                                                    <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="h-full w-full object-cover">
                                                @else
                                                    <i class="fa-solid fa-pizza-slice"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $producto['nombre'] }}</p>
                                                <p class="mt-0.5 max-w-xs truncate text-xs text-slate-400">{{ $producto['descripcion'] ?? 'Sin descripción' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $producto['categoria']['nombre'] ?? __('viewAdmin/productos_admin.index.sin_categoria') }}</td>
                                    <td class="px-5 py-4 font-bold text-slate-900">₡{{ number_format($producto['precio'], 0) }}</td>
                                    <td class="px-5 py-4">
                                        @if($producto['estado'])
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('viewAdmin/productos_admin.index.activo') }}</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ __('viewAdmin/productos_admin.index.inactivo') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.productos.edit', $producto['id']) }}" data-show-loading class="inline-flex h-9 items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                                <i class="fa-solid fa-pen"></i>{{ __('viewAdmin/productos_admin.index.editar') }}
                                            </a>
                                            <form action="{{ route('admin.productos.destroy', $producto['id']) }}" method="POST" data-confirm="{{ __('viewAdmin/productos_admin.index.confirmar_eliminar') }}" data-show-loading>
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-full border border-red-100 bg-red-50 px-4 text-xs font-bold text-red-600 transition hover:bg-red-100">
                                                    <i class="fa-solid fa-trash"></i>{{ __('viewAdmin/productos_admin.index.eliminar') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400">{{ __('viewAdmin/productos_admin.index.vacio') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
