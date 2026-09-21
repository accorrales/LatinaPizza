export function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('hidden');
}

export function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('hidden');
}

function initSiteHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;

    const syncHeaderState = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    syncHeaderState();
    window.addEventListener('scroll', syncHeaderState, { passive: true });
}

function initMobileMenu() {
    const toggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('mobile-menu');
    const panel = menu?.querySelector('[data-mobile-menu-panel]');
    if (!toggle || !menu || !panel) return;

    let closeTimer = null;

    const setExpanded = expanded => {
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        menu.setAttribute('aria-hidden', expanded ? 'false' : 'true');
        toggle.classList.toggle('is-open', expanded);
        document.body.classList.toggle('overflow-hidden', expanded);
    };

    const openMobileMenu = () => {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        menu.classList.remove('hidden');
        setExpanded(true);
        window.requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
    };

    const closeMobileMenu = () => {
        if (menu.classList.contains('hidden')) return;

        panel.classList.add('translate-x-full');
        setExpanded(false);
        closeTimer = window.setTimeout(() => {
            menu.classList.add('hidden');
            closeTimer = null;
        }, 300);
    };

    toggle.addEventListener('click', () => {
        if (menu.classList.contains('hidden')) {
            openMobileMenu();
        } else {
            closeMobileMenu();
        }
    });

    menu.querySelectorAll('[data-mobile-menu-close]').forEach(control => {
        control.addEventListener('click', closeMobileMenu);
    });

    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeMobileMenu();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) closeMobileMenu();
    });
}

export function initGlobalUi() {
    window.mostrarLoading = showLoading;
    window.ocultarLoading = hideLoading;

    initSiteHeader();
    initMobileMenu();

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
