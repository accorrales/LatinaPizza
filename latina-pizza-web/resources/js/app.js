import './bootstrap';
import Alpine from 'alpinejs';
import AOS from 'aos';
import Swiper from 'swiper/bundle';
import '@fortawesome/fontawesome-free/css/all.css';
import 'aos/dist/aos.css';
import 'swiper/css/bundle';

window.Alpine = Alpine;
window.Swiper = Swiper;

async function bootApplication() {
    if (document.getElementById('chartDaily')) {
        const { default: Chart } = await import('chart.js/auto');
        window.Chart = Chart;
    }

    if (document.getElementById('map')) {
        const [maplibregl, { default: circle }] = await Promise.all([
            import('maplibre-gl'),
            import('@turf/circle'),
            import('maplibre-gl/dist/maplibre-gl.css'),
        ]);
        window.maplibregl = maplibregl;
        window.turf = { circle };
        window.dispatchEvent(new Event('latina:maps-ready'));
    }

    Alpine.start();

    const initializeAnimations = () => AOS.init({ duration: 800, once: true, easing: 'ease-in-out' });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAnimations, { once: true });
    } else {
        initializeAnimations();
    }
}

bootApplication();
