@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="text-2xl font-bold mb-4">Catálogo de Productos 🍕</h2>

        @if(session('error'))
            <div class="bg-red-200 p-2 rounded mb-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($productos as $producto)
                <div class="bg-white rounded shadow p-4">
                    <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="w-full h-40 object-cover rounded mb-2">
                    <h3 class="text-lg font-semibold">{{ $producto['nombre'] }}</h3>
                    <p class="text-gray-600">{{ $producto['descripcion'] }}</p>
                    <p class="text-red-600 font-bold mt-2">₡{{ number_format($producto['precio'], 2) }}</p>
                    <form action="{{ route('carrito.agregar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="producto_id" value="{{ $producto['id'] }}">
                        <input type="number" name="cantidad" value="1" min="1" class="form-control mb-2">
                        <button type="submit" class="btn btn-success">Agregar al carrito</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection




