@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-slate-100 bg-gradient-to-br from-[#071426] via-[#0B2344] to-blue-700 px-6 py-7 text-white sm:px-8 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold text-blue-100"><i class="fa-solid fa-users"></i> Accesos</div>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">{{ __('viewAdmin/usuarios_admin.index.titulo') }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-blue-100/80">Administrá clientes, personal de cocina y administradores sin perder de vista permisos y sucursales.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Admins</p><p class="mt-1 text-xl font-black">{{ collect($usuarios)->where('role', 'admin')->count() }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Cocina</p><p class="mt-1 text-xl font-black">{{ collect($usuarios)->where('role', 'cocina')->count() }}</p></div>
                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-[0.14em] text-blue-100/70">Clientes</p><p class="mt-1 text-xl font-black">{{ collect($usuarios)->where('role', 'cliente')->count() }}</p></div>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif
            @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif

            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            <tr>
                                <th class="px-5 py-4">{{ __('viewAdmin/usuarios_admin.index.id') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/usuarios_admin.index.nombre') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/usuarios_admin.index.correo') }}</th>
                                <th class="px-5 py-4">{{ __('viewAdmin/usuarios_admin.index.rol') }}</th>
                                <th class="px-5 py-4 text-right">{{ __('viewAdmin/usuarios_admin.index.acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($usuarios as $usuario)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4 text-xs font-bold text-slate-400">#{{ $usuario['id'] }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#071426] text-sm font-black text-white">{{ strtoupper(substr($usuario['name'], 0, 1)) }}</div>
                                            <div><p class="font-bold text-slate-900">{{ $usuario['name'] }}</p><p class="mt-0.5 text-xs text-slate-400">Usuario del sistema</p></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $usuario['email'] }}</td>
                                    <td class="px-5 py-4">
                                        @if($usuario['role'] === 'admin')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-shield-halved"></i>{{ __('viewAdmin/usuarios_admin.index.rol_admin') }}</span>
                                        @elseif($usuario['role'] === 'cocina')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700"><i class="fa-solid fa-fire-burner"></i>Cocina</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700"><i class="fa-solid fa-user"></i>{{ __('viewAdmin/usuarios_admin.index.rol_cliente') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.usuarios.edit', $usuario['id']) }}" data-show-loading class="inline-flex h-9 items-center gap-2 rounded-full bg-blue-50 px-4 text-xs font-bold text-blue-700 transition hover:bg-blue-100"><i class="fa-solid fa-pen"></i>{{ __('viewAdmin/usuarios_admin.index.editar') }}</a>
                                            <form action="{{ route('admin.usuarios.destroy', $usuario['id']) }}" method="POST" class="inline" data-confirm="{{ __('viewAdmin/usuarios_admin.index.confirmar_eliminar') }}" data-show-loading>
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-full bg-red-50 px-4 text-xs font-bold text-red-600 transition hover:bg-red-100"><i class="fa-solid fa-trash"></i>{{ __('viewAdmin/usuarios_admin.index.eliminar') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-16 text-center text-sm text-slate-400">{{ __('viewAdmin/usuarios_admin.index.vacio') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (($pagination['last_page'] ?? 1) > 1)
                <nav class="mt-6 flex items-center justify-center gap-3" aria-label="Paginación de usuarios">
                    @if ($pagination['current_page'] > 1)<a class="inline-flex h-10 items-center rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.usuarios.index', ['page' => $pagination['current_page'] - 1]) }}">Anterior</a>@endif
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500">Página {{ $pagination['current_page'] }} de {{ $pagination['last_page'] }}</span>
                    @if ($pagination['current_page'] < $pagination['last_page'])<a class="inline-flex h-10 items-center rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-50" href="{{ route('admin.usuarios.index', ['page' => $pagination['current_page'] + 1]) }}">Siguiente</a>@endif
                </nav>
            @endif
        </div>
    </section>
</div>
@endsection
