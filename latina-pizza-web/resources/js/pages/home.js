import Swiper from 'swiper/bundle';

function safeImageUrl(value) {
    try {
        const url = new URL(String(value || ''), window.location.origin);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch {
        return '';
    }
}

export function initHomePage() {
    const wrapper = document.getElementById('carrusel-promos');
    const root = document.querySelector('[data-home-page]');
    if (!wrapper || !root) return;

    const apiBase = root.dataset.apiUrl || '';

    fetch(`${apiBase}/api/promociones`, { headers: { Accept: 'application/json' } })
        .then(response => response.json())
        .then(response => {
            if (!response.success || !Array.isArray(response.data)) return;

            response.data.forEach(promo => {
                const promoId = Number(promo.id);
                if (!Number.isInteger(promoId) || promoId < 1) return;

                const slide = document.createElement('div');
                slide.className = 'swiper-slide';

                const card = document.createElement('button');
                card.type = 'button';
                card.className = 'relative group w-full h-full cursor-pointer text-left';
                card.addEventListener('click', () => {
                    if (document.body.dataset.authenticated !== '1') {
                        window.location.assign('/login');
                        return;
                    }
                    document.dispatchEvent(new CustomEvent('latina:open-promotion', { detail: promoId }));
                });

                const image = document.createElement('img');
                image.src = safeImageUrl(promo.imagen);
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

            if (wrapper.children.length) {
                new Swiper('.mySwiper', {
                    loop: true,
                    autoplay: { delay: 4000, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                });
            }
        })
        .catch(error => console.error('Error al cargar promociones:', error));
}
