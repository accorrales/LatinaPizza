import './bootstrap';
import Alpine from 'alpinejs';
import AOS from 'aos';
import Swiper from 'swiper/bundle';
import '@fortawesome/fontawesome-free/css/all.css';
import 'aos/dist/aos.css';
import 'swiper/css/bundle';

window.Alpine = Alpine;
window.Swiper = Swiper;

const DELIVERY_SELECTOR_URL = '/?cambiar_entrega=1';

function storageGet(key) {
    try {
        return window.localStorage.getItem(key);
    } catch {
        return null;
    }
}

function storageRemove(key) {
    try {
        window.localStorage.removeItem(key);
    } catch {
        // localStorage puede estar bloqueado por el navegador; la navegación debe continuar igual.
    }
}

function goToDeliverySelector({ replace = false } = {}) {
    storageRemove('tipo_pedido');

    if (replace) {
        window.location.replace(DELIVERY_SELECTOR_URL);
        return;
    }

    window.location.assign(DELIVERY_SELECTOR_URL);
}

// El enlace "Tipo Entrega" vive en el layout global. Antes intentaba forzar el
// selector sobre la página actual, pero el modal solo existe en el Home; desde
// /catalogo terminaba en /catalogo?cambiar_entrega=1 sin mostrar nada.
window.abrirSelectorEntrega = () => goToDeliverySelector();

function normalizeDeliverySelectionEntry() {
    const params = new URLSearchParams(window.location.search);
    const forceSelection = params.has('cambiar_entrega')
        || params.has('cambiar')
        || params.get('force') === '1';
    const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

    // Cualquier redirect antiguo como /catalogo?cambiar_entrega=1 se normaliza
    // al Home, que es donde está el selector Express / Para llevar.
    if (forceSelection && currentPath !== '/') {
        goToDeliverySelector({ replace: true });
        return true;
    }

    // No permitimos comenzar a ordenar desde el catálogo sin haber elegido antes
    // el tipo de entrega. Si hay una elección pendiente por login, el Home se
    // encargará de continuar hacia /pickup o /express.
    const hasDeliveryChoice = Boolean(storageGet('tipo_pedido'));
    const hasPendingChoice = Boolean(storageGet('pending_tipo_pedido'));

    if (currentPath === '/catalogo' && !hasDeliveryChoice && !hasPendingChoice) {
        window.location.replace(DELIVERY_SELECTOR_URL);
        return true;
    }

    return false;
}

async function bootApplication() {
    if (normalizeDeliverySelectionEntry()) {
        return;
    }

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
