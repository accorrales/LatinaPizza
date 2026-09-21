@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.catalog-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600"><i class="fa-solid fa-plus"></i> Personalización</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/extras_admin.titulo') }}</h1>
                <p class="mt-2 text-sm text-slate-500">Definí extras y sus precios por tamaño para mantener el cálculo del builder consistente.</p>
            </div>
            <a href="{{ route('admin.extras.create') }}" data-show-loading class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-500 px-6 text-sm font-bold text-white transition hover:bg-red-600"><i class="fa-solid fa-plus"></i>{{ __('viewAdmin/extras_admin.nuevo') }}</a>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500"><tr><th class="px-5 py-4">{{ __('viewAdmin/extras_admin.nombre') }}</th><th class="px-5 py-4">{{ __('viewAdmin/extras_admin.precio_pequena') }}</th><th class="px-5 py-4">{{ __('viewAdmin/extras_admin.precio_mediana') }}</th><th class="px-5 py-4">{{ __('viewAdmin/extras_admin.precio_grande') }}</th><th class="px-5 py-4">{{ __('viewAdmin/extras_admin.precio_extragrande') }}</th><th class="px-5 py-4 text-right">{{ __('viewAdmin/extras_admin.acciones') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($extras as $extra)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4"><div class="flex items-center gap-3"><div class="grid h-10 w-10 place-items-center rounded-2xl bg-red-50 text-red-500"><i class="fa-solid fa-plus"></i></div><span class="font-bold text-slate-900">{{ $extra['nombre'] }}</span></div></td>
                                    <td class="px-5 py-4 font-semibold text-slate-600">₡{{ number_format($extra['precio_pequena'], 0) }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-600">₡{{ number_format($extra['precio_mediana'], 0) }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-600">₡{{ number_format($extra['precio_grande'], 0) }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-600">₡{{ number_format($extra['precio_extragrande'], 0) }}</td>
                                    <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.extras.edit', $extra['id']) }}" data-show-loading class="inline-flex h-9 items-center gap-2 rounded-full bg-blue-50 px-4 text-xs font-bold text-blue-700"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/extras_admin.editar') }}</a><form action="{{ route('admin.extras.destroy', $extra['id']) }}" method="POST" data-show-loading data-confirm="{{ __('viewAdmin/extras_admin.confirmar_eliminar') }}">@csrf @method('DELETE')<button type="submit" class="inline-flex h-9 items-center gap-2 rounded-full bg-red-50 px-4 text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/extras_admin.eliminar') }}</button></form></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-400">{{ __('viewAdmin/extras_admin.sin_registros') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
