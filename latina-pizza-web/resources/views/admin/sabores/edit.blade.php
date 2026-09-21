@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('viewAdmin/sabores_admin.edit.titulo') }}</h1>
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4"><ul class="list-disc ml-6">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form id="formEditarSabor" action="{{ route('admin.sabores.update', $sabor->id) }}" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md" data-show-loading>
        @csrf @method('PUT')
        <div><label for="nombre" class="block font-semibold text-gray-700 mb-1">{{ __('viewAdmin/sabores_admin.edit.nombre') }}</label><input type="text" name="nombre" id="nombre" required value="{{ old('nombre', $sabor->nombre) }}" class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"></div>
        <div><label for="descripcion" class="block font-semibold text-gray-700 mb-1">{{ __('viewAdmin/sabores_admin.edit.descripcion') }}</label><textarea name="descripcion" id="descripcion" rows="3" class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('descripcion', $sabor->descripcion) }}</textarea></div>
        <div><label for="imagen" class="block font-semibold text-gray-700 mb-1">{{ __('viewAdmin/sabores_admin.edit.imagen') }}</label><input type="url" name="imagen" id="imagen" value="{{ old('imagen', $sabor->imagen) }}" class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"></div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.sabores.index') }}" data-show-loading class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">{{ __('viewAdmin/sabores_admin.edit.cancelar') }}</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">{{ __('viewAdmin/sabores_admin.edit.actualizar') }}</button>
        </div>
    </form>
</div>
@endsection
