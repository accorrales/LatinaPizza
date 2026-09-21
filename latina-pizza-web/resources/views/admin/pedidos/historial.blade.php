@extends('layouts.app')

@section('title', 'Historial del Pedido')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-clock-rotate-left"></i> Trazabilidad</div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">Historial del pedido #{{ $pedido_id }}</h1>
                <p class="mt-2 text-sm text-slate-500">Secuencia registrada de cambios de estado.</p>
            </div>
            <a href="{{ route('admin.pedidos.index') }}" data-show-loading class="inline-flex h-11 items-center gap-2 rounded-full border border-slate-200 px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50"><i class="fa-solid fa-arrow-left"></i>Volver</a>
        </div>

        @if(count($historial) > 0)
            <div class="relative ml-2 border-l-2 border-slate-100 pl-7 sm:ml-5 sm:pl-10">
                @foreach($historial as $evento)
                    <div class="relative pb-7 last:pb-0">
                        <span class="absolute -left-[2.18rem] top-1 grid h-5 w-5 place-items-center rounded-full border-4 border-white bg-blue-600 shadow sm:-left-[2.95rem]"></span>
                        <article class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Cambio de estado</p>
                                    <h2 class="mt-1 text-lg font-black capitalize text-slate-900">{{ $evento['estado'] }}</h2>
                                </div>
                                <time class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500"><i class="fa-regular fa-calendar"></i>{{ \Carbon\Carbon::parse($evento['created_at'])->format('d/m/Y H:i') }}</time>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 py-16 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-xl text-slate-300 shadow-sm"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <p class="mt-4 text-sm font-semibold text-slate-500">Este pedido no tiene historial registrado.</p>
            </div>
        @endif
    </section>
</div>
@endsection
