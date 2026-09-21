export function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('hidden');
}

export function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('hidden');
}

export function initGlobalUi() {
    window.mostrarLoading = showLoading;
    window.ocultarLoading = hideLoading;

    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');

    if (toggle && menu) {
        const lines = toggle.querySelectorAll('.hamburger-line');
        const closeMobileMenu = () => {
            menu.classList.add('hidden');
            lines[0]?.classList.remove('rotate-45');
            lines[1]?.classList.remove('opacity-0');
            lines[2]?.classList.remove('-rotate-45');
        };

        toggle.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            lines[0]?.classList.toggle('rotate-45');
            lines[1]?.classList.toggle('opacity-0');
            lines[2]?.classList.toggle('-rotate-45');
        });

        menu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));
    }

    document.addEventListener('click', event => {
        const deliveryLink = event.target.closest('[data-delivery-selector]');
        if (deliveryLink) {
            event.preventDefault();
            window.abrirSelectorEntrega?.();
            return;
        }

        const loadingTrigger = event.target.closest('[data-show-loading]');
        if (loadingTrigger && !loadingTrigger.closest('form')) {
            showLoading();
        }
    });

    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        const confirmation = form.dataset.confirm;
        if (confirmation && !window.confirm(confirmation)) {
            event.preventDefault();
            return;
        }

        if (form.hasAttribute('data-show-loading')) {
            showLoading();
        }
    });

    document.addEventListener('change', event => {
        const control = event.target;
        if (control instanceof HTMLSelectElement && control.hasAttribute('data-auto-submit')) {
            control.form?.requestSubmit();
        }
    });
}
