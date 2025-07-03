@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">🍕 Gestión de Productos</h1>

    <a href="{{ route('admin.productos.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded mb-4 inline-block">+ Nuevo Producto</a>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
    @endif

    <table class="table-auto w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Nombre</th>
                <th class="px-4 py-2">Precio</th>
                <th class="px-4 py-2">Categoría</th>
                <th class="px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $producto['nombre'] }}</td>
                    <td class="px-4 py-2">₡{{ number_format($producto['precio'], 2) }}</td>
                    <td class="px-4 py-2">{{ $producto['categoria']['nombre'] ?? 'Sin categoría' }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.productos.edit', $producto['id']) }}" class="text-blue-600 hover:underline">Editar</a>
                         <form action="{{ route('admin.productos.destroy', $producto['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-2">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

