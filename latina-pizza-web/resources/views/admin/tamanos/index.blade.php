@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('viewAdmin/tamanos_admin.index.titulo') }}</h1>

    {{-- Mensajes de sesión --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 border border-green-300 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 border border-red-300 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Botón para crear nuevo tamaño --}}
    <div class="mb-4 text-right">
        <a href="{{ route('admin.tamanos.create') }}" onclick="mostrarLoading()"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
            {{ __('viewAdmin/tamanos_admin.index.nuevo') }}
        </a>
    </div>

    {{-- Tabla de tamaños --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">{{ __('viewAdmin/tamanos_admin.index.nombre') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">{{ __('viewAdmin/tamanos_admin.index.precio_base') }}</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">{{ __('viewAdmin/tamanos_admin.index.acciones') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                @forelse ($tamanos as $tamano)
                    <tr>
                        <td class="px-6 py-4">{{ $tamano['nombre'] }}</td>
                        <td class="px-6 py-4">₡{{ number_format($tamano['precio_base'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.tamanos.edit', $tamano['id']) }}"
                               onclick="mostrarLoading()"
                               class="text-blue-600 hover:underline mr-3">
                                {{ __('viewAdmin/tamanos_admin.index.editar') }}
                            </a>
                            <form action="{{ route('admin.tamanos.destroy', $tamano['id']) }}" method="POST"
                                  class="inline-block"
                                  onsubmit="mostrarLoading(); return confirm('{{ __('viewAdmin/tamanos_admin.index.confirmar_eliminar') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    {{ __('viewAdmin/tamanos_admin.index.eliminar') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                            {{ __('viewAdmin/tamanos_admin.index.vacio') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
