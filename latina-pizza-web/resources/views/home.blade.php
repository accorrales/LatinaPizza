@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" data-home-page data-api-url="{{ config('app.api_url') }}">
    <div class="swiper mySwiper mb-10 rounded-xl overflow-hidden shadow-xl">
        <div class="swiper-wrapper" id="carrusel-promos"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>

    @include('catalogo.partials.menu', [
        'sabores' => $sabores,
        'categorias' => $categorias,
        'categoriaSeleccionada' => $categoriaSeleccionada,
        'promociones' => $promociones
    ])
</div>

@once
<div
    x-data="deliveryModal"
    x-show="abierto"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60"
>
    <div class="bg-white rounded-xl p-8 w-full max-w-md shadow-lg text-center">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">{{ __('catalogo.como_recibir_pedido') }}</h2>
        <p class="text-gray-600 mb-6">{{ __('catalogo.selecciona_opcion') }}</p>

        <div class="flex flex-col gap-4">
            <button type="button" @click="choose('pickup')" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                {{ __('catalogo.para_llevar') }}
            </button>
            <button type="button" @click="choose('express')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                {{ __('catalogo.express') }}
            </button>
        </div>
    </div>
</div>
@endonce
@endsection
