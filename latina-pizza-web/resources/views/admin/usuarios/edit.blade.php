@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-1 sm:px-2">
    @include('admin.partials.operations-nav')

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,.65fr)]">
        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-8 flex items-start justify-between gap-4">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><i class="fa-solid fa-user-pen"></i> Cuenta</div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ __('viewAdmin/usuarios_admin.edit.titulo') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">Editá identidad, rol y sucursal asignada manteniendo intactas las reglas de acceso.</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" data-show-loading class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 px-4 text-xs font-bold text-slate-600"><i class="fa-solid fa-arrow-left"></i>Volver</a>
            </div>

            @if(session('error'))<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('admin.usuarios.update', $usuario['id']) }}" class="space-y-6" data-show-loading>
                @csrf @method('PUT')
                <div><label for="name" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/usuarios_admin.edit.nombre') }}</label><input type="text" name="name" id="name" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400" value="{{ old('name', $usuario['name']) }}" required></div>
                <div><label for="email" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/usuarios_admin.edit.correo') }}</label><input type="email" name="email" id="email" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400" value="{{ old('email', $usuario['email']) }}" required></div>

                <div class="rounded-3xl border border-blue-100 bg-[#F6F9FF] p-5 sm:p-6">
                    <div class="mb-5"><p class="font-black text-slate-900">Acceso operativo</p><p class="mt-1 text-xs text-slate-500">El rol define qué módulos puede usar esta cuenta.</p></div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="role" class="mb-2 block text-sm font-bold text-slate-700">{{ __('viewAdmin/usuarios_admin.edit.rol') }}</label>
                            <select name="role" id="role" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400" required>
                                <option value="admin" @selected(old('role', $usuario['role']) === 'admin')>{{ __('viewAdmin/usuarios_admin.index.rol_admin') }}</option>
                                <option value="cliente" @selected(old('role', $usuario['role']) === 'cliente')>{{ __('viewAdmin/usuarios_admin.index.rol_cliente') }}</option>
                                <option value="cocina" @selected(old('role', $usuario['role']) === 'cocina')>Cocina</option>
                            </select>
                        </div>
                        <div>
                            <label for="sucursal_id" class="mb-2 block text-sm font-bold text-slate-700">Sucursal asignada</label>
                            <select name="sucursal_id" id="sucursal_id" class="w-full rounded-2xl border-blue-100 bg-white px-4 py-3 text-sm focus:border-blue-400 focus:ring-blue-400">
                                <option value="">Sin sucursal</option>
                                @foreach($sucursales as $sucursal)<option value="{{ $sucursal['id'] }}" @selected(old('sucursal_id', $usuario['sucursal_id'] ?? null) == $sucursal['id'])>{{ $sucursal['nombre'] }}</option>@endforeach
                            </select>
                            <p class="mt-2 text-xs text-slate-500">Es obligatoria para el personal de cocina.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.usuarios.index') }}" data-show-loading class="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 px-6 text-sm font-bold text-slate-600">Cancelar</a><button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-blue-600 px-7 text-sm font-bold text-white transition hover:bg-blue-700"><i class="fa-solid fa-floppy-disk"></i>{{ __('viewAdmin/usuarios_admin.edit.guardar') }}</button></div>
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-[2rem] bg-[#071426] p-6 text-white shadow-xl shadow-slate-950/10">
                <div class="grid h-14 w-14 place-items-center rounded-full bg-white text-xl font-black text-[#071426]">{{ strtoupper(substr($usuario['name'], 0, 1)) }}</div>
                <h2 class="mt-5 text-xl font-black">{{ $usuario['name'] }}</h2>
                <p class="mt-1 text-sm text-slate-300">{{ $usuario['email'] }}</p>
                <div class="mt-5 rounded-2xl bg-white/10 p-4 text-sm"><span class="text-slate-400">Rol actual</span><p class="mt-1 font-bold capitalize text-white">{{ $usuario['role'] }}</p></div>
            </div>
            <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-800"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Cambiar roles puede modificar acceso a administración, cocina y datos operativos.</div>
        </aside>
    </div>
</div>
@endsection
