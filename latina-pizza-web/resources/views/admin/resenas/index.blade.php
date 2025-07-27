@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Mantenimiento de Reseñas</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
        <thead class="bg-gray-200 text-gray-600">
            <tr>
                <th class="py-2 px-4 text-left">Sabor</th>
                <th class="py-2 px-4 text-left">Usuario</th>
                <th class="py-2 px-4 text-left">Comentario</th>
                <th class="py-2 px-4 text-left">Calificación</th>
                <th class="py-2 px-4 text-left">Fecha</th>
                <th class="py-2 px-4">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            @foreach($sabores as $sabor)
                @foreach($sabor['resenas'] as $resena)
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ $sabor['nombre'] }}</td>
                        <td class="py-2 px-4">{{ $resena['user']['name'] ?? 'Usuario desconocido' }}</td>
                        <td class="py-2 px-4">{{ $resena['comentario'] }}</td>
                        <td class="py-2 px-4">{{ $resena['calificacion'] }} ★</td>
                        <td class="py-2 px-4">{{ \Carbon\Carbon::parse($resena['created_at'])->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-4">
                            <form action="{{ route('admin.resenas.destroy', $resena['id']) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Confirmar eliminación')" class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endsection
