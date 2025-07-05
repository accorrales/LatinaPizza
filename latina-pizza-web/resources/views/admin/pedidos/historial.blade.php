@extends('layouts.app')

@section('title', 'Historial del Pedido')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4">
    <h2 class="text-2xl font-bold text-purple-700 mb-6">📚 Historial del Pedido #{{ $pedido_id }}</h2>

    @if(count($historial) > 0)
        <ul class="bg-white shadow rounded divide-y">
            @foreach($historial as $evento)
                <li class="px-4 py-3 flex justify-between text-sm">
                    <span class="text-gray-700">
                        <strong>{{ ucfirst($evento['estado']) }}</strong>
                    </span>
                    <span class="text-gray-500 italic">
                        {{ \Carbon\Carbon::parse($evento['created_at'])->format('d/m/Y H:i') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-center text-gray-600">Este pedido no tiene historial registrado.</div>
    @endif

    <a href="{{ route('admin.pedidos.index') }}" class="mt-6 inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
        ← Volver a la lista
    </a>
</div>
@endsection
