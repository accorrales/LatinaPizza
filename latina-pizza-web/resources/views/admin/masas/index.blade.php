@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold text-red-600 mb-6">{{ __('viewAdmin/masas_admin.titulo') }}</h1>

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

    {{-- Botón para crear nueva masa --}}
    <div class="mb-4 text-right">
        <a href="{{ route('admin.masas.create') }}" onclick="mostrarLoading()"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
            {{ __('viewAdmin/masas_admin.nuevo') }}
        </a>
    </div>

    {{-- Tabla de masas --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">{{ __('viewAdmin/masas_admin.tipo') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-700">{{ __('viewAdmin/masas_admin.precio_extra') }}</th>
                    <th class="px-6 py-3 text-center text-sm font-bold text-gray-700">{{ __('viewAdmin/masas_admin.acciones') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                @forelse ($masas as $masa)
                    <tr>
                        <td class="px-6 py-4">{{ $masa['tipo'] }}</td>
                        <td class="px-6 py-4">₡{{ number_format($masa['precio_extra'], 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.masas.edit', $masa['id']) }}" onclick="mostrarLoading()"
                               class="text-blue-600 hover:underline mr-3">
                                {{ __('viewAdmin/masas_admin.editar') }}
                            </a>

                            <form action="{{ route('admin.masas.destroy', $masa['id']) }}" method="POST" class="inline-block"
                                  onsubmit="mostrarLoading(); return confirm('{{ __('viewAdmin/masas_admin.confirmar_eliminar') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    {{ __('viewAdmin/masas_admin.eliminar') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                            {{ __('viewAdmin/masas_admin.sin_registros') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection



