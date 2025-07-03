@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">👥 Lista de Usuarios</h1>

    @if(session('error'))
        <div class="bg-red-200 text-red-800 p-3 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded shadow">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-2 px-4 border-b">ID</th>
                    <th class="text-left py-2 px-4 border-b">Nombre</th>
                    <th class="text-left py-2 px-4 border-b">Correo</th>
                    <th class="text-left py-2 px-4 border-b">Rol</th>
                    <th class="text-left py-2 px-4 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $usuario['id'] }}</td>
                        <td class="py-2 px-4 border-b">{{ $usuario['name'] }}</td>
                        <td class="py-2 px-4 border-b">{{ $usuario['email'] }}</td>
                        <td class="py-2 px-4 border-b">
                            @if($usuario['role'] === 'admin')
                                <span class="bg-blue-200 text-blue-800 px-2 py-1 rounded text-sm">Admin</span>
                            @else
                                <span class="bg-green-200 text-green-800 px-2 py-1 rounded text-sm">Cliente</span>
                            @endif
                        </td>
                        <td class="py-2 px-4 border-b space-x-2">
                            <a href="{{ route('admin.usuarios.edit', $usuario['id']) }}" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">Editar</a>
                            <form action="{{ route('admin.usuarios.destroy', $usuario['id']) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection




