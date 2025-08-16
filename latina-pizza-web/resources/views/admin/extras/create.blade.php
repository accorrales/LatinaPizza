@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-extrabold mb-6 text-center text-red-600">
        {{ __('viewAdmin/extras_admin.titulo_crear') }}
    </h2>

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form id="formCrearExtra" method="POST" action="{{ route('admin.extras.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="nombre" class="block font-semibold mb-1 text-gray-700">
                {{ __('viewAdmin/extras_admin.nombre') }} <span class="text-red-500">{{ __('viewAdmin/extras_admin.requerido') }}</span>
            </label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @error('nombre') border-red-500 @enderror"
                required>
            @error('nombre')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.precio_pequena') }}</label>
                <input type="number" step="0.01" name="precio_pequena" value="{{ old('precio_pequena') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_pequena') border-red-500 @enderror">
                @error('precio_pequena')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.precio_mediana') }}</label>
                <input type="number" step="0.01" name="precio_mediana" value="{{ old('precio_mediana') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_mediana') border-red-500 @enderror">
                @error('precio_mediana')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.precio_grande') }}</label>
                <input type="number" step="0.01" name="precio_grande" value="{{ old('precio_grande') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_grande') border-red-500 @enderror">
                @error('precio_grande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold mb-1 text-gray-700">{{ __('viewAdmin/extras_admin.precio_extragrande') }}</label>
                <input type="number" step="0.01" name="precio_extragrande" value="{{ old('precio_extragrande') }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 @error('precio_extragrande') border-red-500 @enderror">
                @error('precio_extragrande')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('admin.extras.index') }}" onclick="mostrarLoading()"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('viewAdmin/extras_admin.volver') }}
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearExtra')"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow inline-flex items-center">
                <i class="fas fa-save mr-2"></i> {{ __('viewAdmin/extras_admin.guardar') }}
            </button>
        </div>
    </form>
</div>
@endsection

