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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch('http://127.0.0.1:8001/api/promociones')
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const promos = response.data;
                const wrapper = document.getElementById('carrusel-promos');

                promos.forEach(promo => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.innerHTML = `
                        <div class="relative group w-full h-full cursor-pointer" onclick="manejarClickPromocion(${promo.id})">
                            <img src="${promo.imagen}" alt="${promo.nombre}"
                                class="w-full h-56 sm:h-64 md:h-80 lg:h-[32rem] object-cover rounded-xl transition duration-300">

                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-xl">
                                <span class="text-white text-xl sm:text-2xl font-bold animate-pulse">👆 Pick me para comprar</span>
                            </div>
                        </div>
                    `;
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