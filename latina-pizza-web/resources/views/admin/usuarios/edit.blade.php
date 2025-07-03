@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-lg">
    <h1 class="text-2xl font-bold mb-6">✏️ Editar Usuario</h1>

    @if(session('error'))
        <div class="bg-red-200 text-red-800 p-3 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.usuarios.update', $usuario['id']) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold mb-1">Nombre</label>
            <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2" value="{{ $usuario['name'] }}" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-semibold mb-1">Correo</label>
            <input type="email" name="email" id="email" class="w-full border rounded px-3 py-2" value="{{ $usuario['email'] }}" required>
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-semibold mb-1">Rol</label>
            <select name="role" id="role" class="w-full border rounded px-3 py-2" required>
                <option value="admin" {{ $usuario['role'] === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="cliente" {{ $usuario['role'] === 'cliente' ? 'selected' : '' }}>Cliente</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Guardar Cambios
        </button>
    </form>
</div>
@endsection
