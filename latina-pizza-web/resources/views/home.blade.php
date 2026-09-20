@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- ✅ Carrusel de imágenes (responsive con altura variable) -->
    <div class="swiper mySwiper mb-10 rounded-xl overflow-hidden shadow-xl">
        <div class="swiper-wrapper" id="carrusel-promos">
            <!-- Aquí se cargarán dinámicamente los slides -->
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>

    <!-- ✅ Menú (ya responsivo en partials.menu) -->
    @include('catalogo.partials.menu', [
        'sabores' => $sabores,
        'categorias' => $categorias,
        'categoriaSeleccionada' => $categoriaSeleccionada,
        'promociones' => $promociones
    ])
</div>

@once
<!-- 🧭 Modal de selección: Express / Pickup -->
<div
  x-data="entregaModal()"
  x-init="init()"
  x-show="abierto"
  x-cloak
  x-transition
  class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60"
>
  <div class="bg-white rounded-xl p-8 w-full max-w-md shadow-lg text-center">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">{{ __('catalogo.como_recibir_pedido') }}</h2>
    <p class="text-gray-600 mb-6">{{ __('catalogo.selecciona_opcion') }}</p>

    <div class="flex flex-col gap-4">
      <!-- Pickup -->
      <button
        @click="choose('pickup')"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
      >
        {{ __('catalogo.para_llevar') }}
      </button>

      <!-- Express -->
      <button
        @click="choose('express')"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
      >
        {{ __('catalogo.express') }}
      </button>
    </div>
  </div>
</div>
@endonce

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const API_BASE = '{{ config('app.api_url') }}';

    fetch(`${API_BASE}/api/promociones`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const promos = response.data;
                const wrapper = document.getElementById('carrusel-promos');

                promos.forEach(promo => {
                    const promoId = Number(promo.id);
                    if (!Number.isInteger(promoId) || promoId < 1) return;

                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';

                    const card = document.createElement('div');
                    card.className = 'relative group w-full h-full cursor-pointer';
                    card.addEventListener('click', () => manejarClickPromocion(promoId));

                    const image = document.createElement('img');
                    try {
                        const imageUrl = new URL(String(promo.imagen || ''), window.location.origin);
                        image.src = ['http:', 'https:'].includes(imageUrl.protocol) ? imageUrl.href : '';
                    } catch {
                        image.src = '';
                    }
                    image.alt = String(promo.nombre || 'Promoción');
                    image.className = 'w-full h-56 sm:h-64 md:h-80 lg:h-[32rem] object-cover rounded-xl transition duration-300';

                    const overlay = document.createElement('div');
                    overlay.className = 'absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-xl';
                    const label = document.createElement('span');
                    label.className = 'text-white text-xl sm:text-2xl font-bold animate-pulse';
                    label.textContent = '👆 Pick me para comprar';

                    overlay.appendChild(label);
                    card.append(image, overlay);
                    slide.appendChild(card);
                    wrapper.appendChild(slide);
                });

                // Inicia Swiper una vez que las imágenes están cargadas
                new Swiper(".mySwiper", {
                    loop: true,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar promociones:', error);
        });
});

function manejarClickPromocion(promoId) {
    console.log("¿Autenticado?", window.isAuthenticated); // ✅ DEBUG
    if (window.isAuthenticated) {
        abrirModalPromocion(promoId);
    } else {
        window.location.href = '/login';
    }
}

</script>
@endpush
