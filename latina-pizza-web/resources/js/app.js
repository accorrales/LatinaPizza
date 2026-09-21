import './bootstrap';
import AOS from 'aos';
import '@fortawesome/fontawesome-free/css/all.css';
import 'aos/dist/aos.css';
import 'swiper/css/bundle';

import { initGlobalUi } from './core/ui';
import {
    installGlobalDeliverySelector,
    normalizeDeliverySelectionEntry,
    registerAlpineComponents,
} from './core/alpine-components';
import { initAdminPages } from './pages/admin';
import { initCatalogPage } from './pages/catalog';
import { initCheckoutPage } from './pages/checkout';
import { initDeliveryPages } from './pages/delivery';
import { initExpressMap } from './pages/express-map';
import { initHomePage } from './pages/home';
import { registerKitchenComponent } from './pages/kitchen';
import { registerSalesDashboard } from './pages/sales-dashboard';

const ALPINE_CSP_URL = 'https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.17.3/dist/module.esm.js';

async function loadCspAlpine() {
    const module = await import(/* @vite-ignore */ ALPINE_CSP_URL);
    return module.default;
}

async function bootApplication() {
    installGlobalDeliverySelector();
    if (normalizeDeliverySelectionEntry()) return;

    initGlobalUi();
    initAdminPages();
    initCatalogPage();
    initCheckoutPage();
    initDeliveryPages();
    initHomePage();

    if (document.getElementById('map')) {
        try {
            await initExpressMap();
        } catch (error) {
            console.error('No se pudo inicializar el mapa:', error);
        }
    }

    const Alpine = await loadCspAlpine();
    registerAlpineComponents(Alpine);
    registerKitchenComponent(Alpine);
    registerSalesDashboard(Alpine);
    window.Alpine = Alpine;
    Alpine.start();

    AOS.init({ duration: 800, once: true, easing: 'ease-in-out' });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => bootApplication(), { once: true });
} else {
    bootApplication();
}
