@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $freeCount = collect($mesas)->where('estado', 'disponible')->count();
    $occupiedCount = collect($mesas)->where('estado', 'ocupada')->count();
    $zones = collect($mesas)->groupBy(fn ($mesa) => $mesa['zona'] ?: 'Salón principal');
    $canOpenTables = in_array($user->role, ['admin', 'gerente', 'mesero'], true);
@endphp

<div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] bg-[#071426] text-white shadow-xl shadow-slate-950/10">
        <div class="grid gap-8 p-7 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end lg:p-9">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-blue-100">
                    <i class="fa-solid fa-utensils"></i> Operación de salón
                </div>
                <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Mesas en tiempo real</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                    Abrí una mesa, enviá rondas a cocina, seguí el servicio y cerrá la cuenta con trazabilidad completa.
                </p>
                @if($selectedBranch)
                    <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-bold text-white">
                        <i class="fa-solid fa-location-dot text-red-400"></i>{{ $selectedBranch['nombre'] }}
                    </p>
                @endif
            </div>

            @if($user->role === 'admin' && $sucursales->isNotEmpty())
                <form method="GET" action="{{ route('salon.index') }}" class="min-w-[260px] rounded-2xl bg-white/10 p-4">
                    <label for="sucursal_id" class="mb-2 block text-xs font-bold uppercase tracking-[0.15em] text-slate-300">Sucursal</label>
                    <div class="flex gap-2">
                        <select id="sucursal_id" name="sucursal_id" class="min-w-0 flex-1 rounded-xl border-white/10 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal['id'] }}" @selected((int) $selectedBranchId === (int) $sucursal['id'])>{{ $sucursal['nombre'] }}</option>
                            @endforeach
                        </select>
                        <button class="grid h-10 w-10 place-items-center rounded-xl bg-blue-500 text-white transition hover:bg-blue-400" aria-label="Cambiar sucursal">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-400">Mesas activas</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ count($mesas) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-600">Disponibles</p>
            <p class="mt-2 text-3xl font-black text-emerald-900">{{ $freeCount }}</p>
        </div>
        <div class="rounded-3xl border border-red-200 bg-red-50 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-red-600">Ocupadas</p>
            <p class="mt-2 text-3xl font-black text-red-900">{{ $occupiedCount }}</p>
        </div>
    </div>

    @if(in_array($user->role, ['admin', 'gerente'], true) && $selectedBranchId)
        <details class="group mt-6 rounded-3xl border border-slate-200 bg-white shadow-sm">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 sm:p-6">
                <div>
                    <p class="font-black text-slate-900">Agregar una mesa</p>
                    <p class="mt-1 text-xs text-slate-500">Configuración rápida para esta sucursal.</p>
                </div>
                <span class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 transition group-open:rotate-45"><i class="fa-solid fa-plus"></i></span>
            </summary>
            <form method="POST" action="{{ route('salon.mesas.store') }}" class="grid gap-4 border-t border-slate-100 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-5" data-show-loading>
                @csrf
                <input type="hidden" name="sucursal_id" value="{{ $selectedBranchId }}">
                <div><label class="mb-2 block text-xs font-bold text-slate-600">Número *</label><input name="numero" value="{{ old('numero') }}" required class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm" placeholder="8"></div>
                <div><label class="mb-2 block text-xs font-bold text-slate-600">Nombre</label><input name="nombre" value="{{ old('nombre') }}" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm" placeholder="Ventana"></div>
                <div><label class="mb-2 block text-xs font-bold text-slate-600">Zona</label><input name="zona" value="{{ old('zona') }}" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm" placeholder="Terraza"></div>
                <div><label class="mb-2 block text-xs font-bold text-slate-600">Capacidad *</label><input type="number" min="1" max="50" name="capacidad" value="{{ old('capacidad', 4) }}" required class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm"></div>
                <div class="flex items-end"><button class="inline-flex h-[42px] w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-plus"></i>Crear mesa</button></div>
            </form>
        </details>
    @endif

    @if(empty($mesas))
        <div class="mt-8 rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-slate-100 text-2xl text-slate-400"><i class="fa-solid fa-chair"></i></div>
            <h2 class="mt-5 text-xl font-black text-slate-900">Todavía no hay mesas configuradas</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">Creá las mesas de esta sucursal y aparecerán agrupadas automáticamente por zona.</p>
        </div>
    @else
        <div class="mt-8 space-y-8">
            @foreach($zones as $zone => $zoneTables)
                <section>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Zona</p>
                            <h2 class="mt-1 text-xl font-black text-slate-900">{{ $zone }}</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ $zoneTables->count() }} mesas</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        @foreach($zoneTables as $mesa)
                            @php
                                $occupied = $mesa['estado'] === 'ocupada' && !empty($mesa['session']);
                                $session = $mesa['session'] ?? null;
                                $opened = $session && !empty($session['opened_at']) ? \Carbon\Carbon::parse($session['opened_at']) : null;
                                $minutesOpen = $opened ? (int) $opened->diffInMinutes(now()) : null;
                            @endphp
                            <article class="overflow-hidden rounded-3xl border {{ $occupied ? 'border-red-200 bg-red-50/40' : 'border-emerald-200 bg-emerald-50/30' }} shadow-sm">
                                <div class="flex items-start justify-between gap-4 p-5">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="grid h-11 min-w-11 place-items-center rounded-2xl {{ $occupied ? 'bg-red-500' : 'bg-emerald-500' }} px-3 text-sm font-black text-white">{{ $mesa['numero'] }}</span>
                                            <div>
                                                <h3 class="font-black text-slate-900">{{ $mesa['nombre'] ?: 'Mesa '.$mesa['numero'] }}</h3>
                                                <p class="mt-0.5 text-xs text-slate-500"><i class="fa-solid fa-user-group mr-1"></i>Capacidad {{ $mesa['capacidad'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wide {{ $occupied ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $occupied ? 'Ocupada' : 'Disponible' }}</span>
                                </div>

                                @if($occupied)
                                    <div class="border-t border-red-100 bg-white/70 p-5">
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div class="rounded-2xl bg-white p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Personas</p><p class="mt-1 font-black text-slate-900">{{ $session['personas'] }}</p></div>
                                            <div class="rounded-2xl bg-white p-3"><p class="text-[10px] font-bold uppercase text-slate-400">Tiempo</p><p class="mt-1 font-black text-slate-900">{{ $minutesOpen }} min</p></div>
                                        </div>
                                        <div class="mt-3 rounded-2xl bg-white p-3 text-xs text-slate-600">
                                            <span class="font-bold text-slate-900">Mesero:</span> {{ $session['mesero']['name'] ?? 'Sin asignar' }}
                                        </div>
                                        @if(!empty($session['notas']))<p class="mt-3 rounded-2xl bg-amber-50 p-3 text-xs leading-5 text-amber-800">{{ $session['notas'] }}</p>@endif

                                        @if($user->role !== 'mesero' || (int) ($session['mesero']['id'] ?? 0) === (int) $user->id)
                                            <a href="{{ route('salon.sessions.show', $session['id']) }}" class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-black text-white transition hover:bg-blue-700">
                                                <i class="fa-solid fa-receipt"></i>Gestionar mesa
                                            </a>
                                        @else
                                            <div class="mt-4 rounded-xl bg-slate-100 px-4 py-3 text-center text-xs font-bold text-slate-500">Asignada a otro mesero.</div>
                                        @endif
                                    </div>
                                @else
                                    @if($canOpenTables)
                                        <form method="POST" action="{{ route('salon.mesas.open', $mesa['id']) }}" class="space-y-3 border-t border-emerald-100 bg-white/80 p-5" data-show-loading>
                                            @csrf
                                            <input type="hidden" name="sucursal_id" value="{{ $selectedBranchId }}">
                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold text-slate-600">Personas</label>
                                                <input type="number" min="1" max="50" name="personas" value="1" required class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm">
                                            </div>
                                            @if($user->role !== 'mesero')
                                                <div>
                                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Mesero responsable</label>
                                                    <select name="mesero_user_id" required class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm">
                                                        <option value="" selected disabled>Seleccione un mesero</option>
                                                        @foreach($meseros as $mesero)<option value="{{ $mesero['id'] }}">{{ $mesero['name'] }}</option>@endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <button class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 text-xs font-bold text-white transition hover:bg-emerald-700"><i class="fa-solid fa-play"></i>Abrir mesa</button>
                                        </form>
                                    @else
                                        <div class="border-t border-emerald-100 bg-white/80 p-5 text-center text-xs font-semibold text-slate-500">
                                            <i class="fa-solid fa-cash-register mb-2 block text-lg text-slate-300"></i>
                                            Caja puede gestionar y cobrar mesas ocupadas; la apertura corresponde a salón o gerencia.
                                        </div>
                                    @endif
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>
@endsection
