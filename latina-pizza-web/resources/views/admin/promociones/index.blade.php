@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">Promociones</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('admin.promociones.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Crear Nueva Promoción
        </a>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full text-left">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Descripción</th>
                    <th class="px-4 py-2">Precio Total</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promociones as $promo)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $promo['nombre'] }}</td>
                        <td class="px-4 py-2">{{ $promo['descripcion'] }}</td>
                        <td class="px-4 py-2">₡{{ number_format($promo['precio_total'], 0) }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="{{ route('admin.promociones.edit', $promo['id']) }}" class="text-blue-600 hover:underline">Editar</a>

                            <form action="{{ route('admin.promociones.destroy', $promo['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta promoción?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">No hay promociones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
