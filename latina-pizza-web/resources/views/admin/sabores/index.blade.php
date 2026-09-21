@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600"><i class="fa-solid fa-pizza-slice"></i> Recetas</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/sabores_admin.index.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Gestioná sabores, descripciones e imágenes que alimentan el catálogo de pizzas.</p>
            </div>
            <a href="{{ route('admin.sabores.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/sabores_admin.index.nuevo') }}</a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($sabores as $sabor)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-950/5">
                        <div class="aspect-[16/9] bg-slate-100">
                            @if (!empty($sabor['imagen']))<img src="{{ $sabor['imagen'] }}" alt="{{ $sabor['nombre'] }}" class="h-full w-full object-cover">@else<div class="grid h-full place-items-center text-4xl text-slate-300"><i class="fa-solid fa-pizza-slice"></i></div>@endif
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-black text-slate-900">{{ $sabor['nombre'] }}</h3>
                            <p class="mt-2 min-h-10 text-sm leading-5 text-slate-500">{{ $sabor['descripcion'] ?? __('viewAdmin/sabores_admin.index.sin_imagen') }}</p>
                            <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                                <a href="{{ route('admin.sabores.edit', $sabor['id']) }}" data-show-loading class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/sabores_admin.index.editar') }}</a>
                                <form action="{{ route('admin.sabores.destroy', $sabor['id']) }}" method="POST" class="flex-1" data-show-loading data-confirm="{{ __('viewAdmin/sabores_admin.index.confirmar_eliminar') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-100"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/sabores_admin.index.eliminar') }}</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-200 py-14 text-center text-sm text-slate-400">{{ __('viewAdmin/sabores_admin.index.vacio') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
