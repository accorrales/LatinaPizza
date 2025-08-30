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
    const API_BASE = 'http://127.0.0.1:8001';

    fetch(`${API_BASE}/api/promociones`)
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

/* =========================
   🔻 Lógica del modal
   ========================= */
function entregaModal() {
  const API_BASE = 'http://127.0.0.1:8001';

  return {
    abierto: false,

    init() {
        const url = new URL(window.location.href);
        const forceByQuery = url.searchParams.get('cambiar_entrega') === '1';
        const forceByFlag  = localStorage.getItem('force_modal_entrega') === '1';
        const hasChoice    = !!localStorage.getItem('tipo_pedido');

        // Abrir si: venimos forzados por query, por flag, o si aún no eligió nunca
        this.abierto = forceByQuery || forceByFlag || !hasChoice;

        // Limpia el query param para que no se repita al navegar
        if (forceByQuery) {
            url.searchParams.delete('cambiar_entrega');
            window.history.replaceState({}, '', url);
        }

        // Limpia la banderita (se usa una sola vez)
        if (forceByFlag) {
            localStorage.removeItem('force_modal_entrega');
        }

        // Exponer función global para abrirlo desde el nav si ya estamos en Home
        window.abrirSelectorEntrega = () => { this.abierto = true; };
        window.__entregaModalInstance = this;
    },

    async choose(tipo) {
      const destino = (tipo === 'pickup') ? '/pickup' : '/express';

      if (window.isAuthenticated) {
        await this.persistTipo(tipo);
        this.abierto = false;
        window.location.href = destino;
      } else {
        // Guarda intención y redirige a login
        localStorage.setItem('pending_tipo_pedido', tipo);
        localStorage.setItem('pending_redirect', destino);
        window.location.href = '/login';
      }
    },

    async persistTipo(tipo) {
      try {
        await fetch(`${API_BASE}/guardar-tipo-pedido`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
          },
          credentials: 'include',
          body: JSON.stringify({ tipo })
        });
      } catch (e) {
        console.warn('No se pudo guardar tipo_pedido en backend:', e);
      }
      localStorage.setItem('tipo_pedido', tipo);
    }
  };
}
</script>
@endpush
