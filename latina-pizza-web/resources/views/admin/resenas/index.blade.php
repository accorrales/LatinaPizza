@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    @php
        $hayResenas = false;
        $totalResenas = 0;
        $sumaCalificaciones = 0;
        if (!empty($sabores)) {
            foreach ($sabores as $s) {
                if (!empty($s['resenas'])) {
                    $hayResenas = true;
                    foreach ($s['resenas'] as $r) {
                        $totalResenas++;
                        $sumaCalificaciones += (int) ($r['calificacion'] ?? 0);
                    }
                }
            }
        }
        $promedio = $totalResenas > 0 ? round($sumaCalificaciones / $totalResenas, 1) : 0;
    @endphp

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-6 border-b border-slate-100 px-6 py-7 sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700"><i class="fa-solid fa-star"></i> Moderación</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/resenas_admin.titulo') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Revisá comentarios y calificaciones asociados a cada sabor.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center sm:min-w-[260px]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Reseñas</p><p class="mt-1 text-2xl font-black text-slate-900">{{ $totalResenas }}</p></div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-500">Promedio</p><p class="mt-1 text-2xl font-black text-amber-700">{{ $promedio }} ★</p></div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif

            @if(!$hayResenas)
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-xl text-slate-300 shadow-sm"><i class="fa-regular fa-star"></i></div>
                    <p class="mt-4 text-sm font-semibold text-slate-500">{{ __('viewAdmin/resenas_admin.sin_resenas') }}</p>
                </div>
            @else
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach($sabores as $sabor)
                        @foreach(($sabor['resenas'] ?? []) as $resena)
                            <article class="rounded-3xl border border-slate-200 bg-white p-5 transition hover:border-amber-200 hover:shadow-lg hover:shadow-amber-950/5 sm:p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#071426] text-sm font-black text-white">{{ strtoupper(substr($resena['user']['name'] ?? '?', 0, 1)) }}</div>
                                        <div class="min-w-0"><p class="truncate font-black text-slate-900">{{ $resena['user']['name'] ?? __('viewAdmin/resenas_admin.usuario_desconocido') }}</p><p class="mt-0.5 text-xs text-slate-400">{{ $sabor['nombre'] }}</p></div>
                                    </div>
                                    <div class="rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ $resena['calificacion'] }} ★</div>
                                </div>

                                <blockquote class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">“{{ $resena['comentario'] }}”</blockquote>

                                <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <time class="text-xs font-semibold text-slate-400"><i class="fa-regular fa-calendar mr-1.5"></i>{{ \Carbon\Carbon::parse($resena['created_at'])->format('d/m/Y H:i') }}</time>
                                    <form action="{{ route('admin.resenas.destroy', $resena['id']) }}" method="POST" data-confirm="{{ __('viewAdmin/resenas_admin.confirmar_eliminacion') }}" data-show-loading>
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-full bg-red-50 px-4 text-xs font-bold text-red-600 transition hover:bg-red-100"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/resenas_admin.eliminar') }}</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
