@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">{{ __('viewAdmin/resenas_admin.titulo') }}</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @php
        $hayResenas = false;
        if (!empty($sabores)) {
            foreach ($sabores as $s) {
                if (!empty($s['resenas'])) { $hayResenas = true; break; }
            }
        }
    @endphp

    @if(!$hayResenas)
        <div class="bg-white p-6 rounded shadow text-gray-600">
            {{ __('viewAdmin/resenas_admin.sin_resenas') }}
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full rounded-lg overflow-hidden">
                <thead class="bg-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 px-4 text-left">{{ __('viewAdmin/resenas_admin.tabla_sabor') }}</th>
                        <th class="py-2 px-4 text-left">{{ __('viewAdmin/resenas_admin.tabla_usuario') }}</th>
                        <th class="py-2 px-4 text-left">{{ __('viewAdmin/resenas_admin.tabla_comentario') }}</th>
                        <th class="py-2 px-4 text-left">{{ __('viewAdmin/resenas_admin.tabla_calificacion') }}</th>
                        <th class="py-2 px-4 text-left">{{ __('viewAdmin/resenas_admin.tabla_fecha') }}</th>
                        <th class="py-2 px-4 text-center">{{ __('viewAdmin/resenas_admin.tabla_acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach($sabores as $sabor)
                        @foreach($sabor['resenas'] as $resena)
                            <tr class="border-b">
                                <td class="py-2 px-4">{{ $sabor['nombre'] }}</td>
                                <td class="py-2 px-4">{{ $resena['user']['name'] ?? __('viewAdmin/resenas_admin.usuario_desconocido') }}</td>
                                <td class="py-2 px-4">{{ $resena['comentario'] }}</td>
                                <td class="py-2 px-4">{{ $resena['calificacion'] }} ★</td>
                                <td class="py-2 px-4">
                                    {{ \Carbon\Carbon::parse($resena['created_at'])->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-2 px-4 text-center">
                                    <form action="{{ route('admin.resenas.destroy', $resena['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('{{ __('viewAdmin/resenas_admin.confirmar_eliminacion') }}')"
                                                class="text-red-600 hover:underline">
                                            {{ __('viewAdmin/resenas_admin.eliminar') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

