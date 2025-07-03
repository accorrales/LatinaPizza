@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Categoría</h1>

    <form method="POST" action="{{ route('admin.categorias.update', $categoria['id']) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="nombre" class="block font-semibold mb-1">Nombre</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $categoria['nombre']) }}" class="w-full border p-2 rounded" required>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar</button>
    </form>
</div>
@endsection
