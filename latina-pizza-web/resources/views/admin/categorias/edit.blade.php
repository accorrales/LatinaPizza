@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">{{ __('viewAdmin/categorias_admin.editar_categoria') }}</h1>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errores de validación --}}
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.categorias.update', $categoria['id']) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Campo Nombre --}}
        <div>
            <label for="nombre" class="block font-semibold mb-1">{{ __('viewAdmin/categorias_admin.nombre') }}</label>
            <input 
                type="text" 
                name="nombre" 
                id="nombre" 
                value="{{ old('nombre', $categoria['nombre']) }}" 
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            >
        </div>

        {{-- Campo Descripción --}}
        <div>
            <label for="descripcion" class="block font-semibold mb-1">{{ __('viewAdmin/categorias_admin.descripcion') }}</label>
            <textarea 
                name="descripcion" 
                id="descripcion" 
                rows="3"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >{{ old('descripcion', $categoria['descripcion'] ?? '') }}</textarea>
        </div>

        {{-- Botones --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                💾 {{ __('viewAdmin/categorias_admin.actualizar') }}
            </button>
            <a href="{{ route('admin.categorias.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                ⬅️ {{ __('viewAdmin/categorias_admin.volver') }}
            </a>
        </div>
    </form>
</div>
@endsection
