@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-3xl font-bold mb-6">{{ __('viewAdmin/promociones_admin.index.titulo') }}</h1>

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
        <a href="{{ route('admin.promociones.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ __('viewAdmin/promociones_admin.index.crear') }}
        </a>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full text-left">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2">{{ __('viewAdmin/promociones_admin.index.nombre') }}</th>
                    <th class="px-4 py-2">{{ __('viewAdmin/promociones_admin.index.descripcion') }}</th>
                    <th class="px-4 py-2">{{ __('viewAdmin/promociones_admin.index.precio_total') }}</th>
                    <th class="px-4 py-2">{{ __('viewAdmin/promociones_admin.index.acciones') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promociones as $promo)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $promo['nombre'] }}</td>
                        <td class="px-4 py-2">{{ $promo['descripcion'] }}</td>
                        <td class="px-4 py-2">₡{{ number_format($promo['precio_total'], 0) }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="{{ route('admin.promociones.edit', $promo['id']) }}"
                               class="text-blue-600 hover:underline">
                                {{ __('viewAdmin/promociones_admin.index.editar') }}
                            </a>

                            <form action="{{ route('admin.promociones.destroy', $promo['id']) }}"
                                  method="POST" class="inline-block"
                                  onsubmit="return confirm('{{ __('viewAdmin/promociones_admin.index.confirmar_eliminar') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    {{ __('viewAdmin/promociones_admin.index.eliminar') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                            {{ __('viewAdmin/promociones_admin.index.vacio') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
