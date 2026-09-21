@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-extrabold mb-6 text-center text-blue-600">{{ __('viewAdmin/extras_admin.titulo_editar') }}</h2>
    @if(session('error'))<div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">{{ session('error') }}</div>@endif

    <form id="formEditarExtra" method="POST" action="{{ route('admin.extras.update', $extra->id) }}" class="space-y-6" data-show-loading>
        @csrf @method('PUT')
        <div>
            <label for="nombre" class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.nombre') }} <span class="text-red-500">{{ __('viewAdmin/extras_admin.requerido') }}</span></label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $extra->nombre) }}" class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror" required>
            @error('nombre')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['precio_pequena', 'precio_mediana', 'precio_grande', 'precio_extragrande'] as $campo)
                <div>
                    <label class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.'.$campo) }}</label>
                    <input type="number" step="0.01" name="{{ $campo }}" value="{{ old($campo, $extra->{$campo}) }}" class="w-full border border-gray-300 rounded px-3 py-2 @error($campo) border-red-500 @enderror">
                    @error($campo)<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('admin.extras.index') }}" data-show-loading class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">{{ __('viewAdmin/extras_admin.cancelar') }}</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow inline-flex items-center"><i class="fas fa-sync-alt mr-2"></i> {{ __('viewAdmin/extras_admin.actualizar') }}</button>
        </div>
    </form>
</div>
@endsection
