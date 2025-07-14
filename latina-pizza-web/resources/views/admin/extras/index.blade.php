@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Extras disponibles</h1>
        <a href="{{ route('admin.extras.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Nuevo Extra
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="table-auto w-full border border-gray-300 text-sm bg-white shadow rounded">
            <thead class="bg-gray-200 text-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-center">₡ Pequeña</th>
                    <th class="px-4 py-2 text-center">₡ Mediana</th>
                    <th class="px-4 py-2 text-center">₡ Grande</th>
                    <th class="px-4 py-2 text-center">₡ Extragrande</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($extras as $extra)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $extra['nombre'] }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($extra['precio_pequena'], 0) }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($extra['precio_mediana'], 0) }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($extra['precio_grande'], 0) }}</td>
                        <td class="px-4 py-2 text-center">₡{{ number_format($extra['precio_extragrande'], 0) }}</td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('admin.extras.edit', $extra['id']) }}" class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs">Editar</a>
                            <form action="{{ route('admin.extras.destroy', $extra['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este extra?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No hay extras registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
