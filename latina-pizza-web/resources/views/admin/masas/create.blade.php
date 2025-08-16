@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('viewAdmin/masas_admin.crear_titulo') }}</h1>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4">
            <ul class="list-disc ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario de creación --}}
    <form id="formCrearMasa" action="{{ route('admin.masas.store') }}" method="POST"
          class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        @csrf

        {{-- Tipo de masa --}}
        <div>
            <label for="tipo" class="block font-semibold text-gray-700 mb-1">
                {{ __('viewAdmin/masas_admin.tipo_label') }}
            </label>
            <input type="text" name="tipo" id="tipo" required value="{{ old('tipo') }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Precio extra --}}
        <div>
            <label for="precio_extra" class="block font-semibold text-gray-700 mb-1">
                {{ __('viewAdmin/masas_admin.precio_extra_label') }}
            </label>
            <input type="number" name="precio_extra" id="precio_extra" step="0.01"
                   value="{{ old('precio_extra', 0) }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.masas.index') }}" onclick="mostrarLoading()"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                {{ __('viewAdmin/masas_admin.cancelar') }}
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearMasa')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
                {{ __('viewAdmin/masas_admin.boton_crear') }}
            </button>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('masas_admin.crear_titulo') }}</h1>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4">
            <ul class="list-disc ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario de creación --}}
    <form id="formCrearMasa" action="{{ route('admin.masas.store') }}" method="POST"
          class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        @csrf

        {{-- Tipo de masa --}}
        <div>
            <label for="tipo" class="block font-semibold text-gray-700 mb-1">
                {{ __('masas_admin.tipo_label') }}
            </label>
            <input type="text" name="tipo" id="tipo" required value="{{ old('tipo') }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Precio extra --}}
        <div>
            <label for="precio_extra" class="block font-semibold text-gray-700 mb-1">
                {{ __('masas_admin.precio_extra_label') }}
            </label>
            <input type="number" name="precio_extra" id="precio_extra" step="0.01"
                   value="{{ old('precio_extra', 0) }}"
                   class="w-full border-gray-300 rounded px-4 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.masas.index') }}" onclick="mostrarLoading()"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                {{ __('masas_admin.cancelar') }}
            </a>
            <button type="submit" onclick="return validarYMostrarLoading('formCrearMasa')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
                {{ __('masas_admin.boton_crear') }}
            </button>
        </div>
    </form>
</div>
@endsection


