const DELIVERY_SELECTOR_URL = '/?cambiar_entrega=1';

function storageGet(key) {
    try {
        return window.localStorage.getItem(key);
    } catch {
        return null;
    }
}

function storageSet(key, value) {
    try {
        window.localStorage.setItem(key, value);
    } catch {
        // El flujo de navegación continúa aunque storage esté bloqueado.
    }
}

function storageRemove(key) {
    try {
        window.localStorage.removeItem(key);
    } catch {
        // El flujo de navegación continúa aunque storage esté bloqueado.
    }
}

export function installGlobalDeliverySelector() {
    window.abrirSelectorEntrega = () => {
        storageRemove('tipo_pedido');
        window.location.assign(DELIVERY_SELECTOR_URL);
    };
}

export function normalizeDeliverySelectionEntry() {
    const params = new URLSearchParams(window.location.search);
    const forceSelection = params.has('cambiar_entrega')
        || params.has('cambiar')
        || params.get('force') === '1';
    const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

    if (forceSelection && currentPath !== '/') {
        storageRemove('tipo_pedido');
        window.location.replace(DELIVERY_SELECTOR_URL);
        return true;
    }

    const hasDeliveryChoice = Boolean(storageGet('tipo_pedido'));
    const hasPendingChoice = Boolean(storageGet('pending_tipo_pedido'));

    if (currentPath === '/catalogo' && !hasDeliveryChoice && !hasPendingChoice) {
        window.location.replace(DELIVERY_SELECTOR_URL);
        return true;
    }

    return false;
}

export function registerAlpineComponents(Alpine) {
    Alpine.data('deliveryChip', () => ({
        t: storageGet('tipo_pedido') || '',
    }));

    Alpine.data('deliveryModal', () => ({
        abierto: false,

        async init() {
            const params = new URLSearchParams(window.location.search);
            const force = params.has('cambiar_entrega')
                || params.has('cambiar')
                || params.get('force') === '1';

            const hasChoice = Boolean(storageGet('tipo_pedido'));
            const pending = storageGet('pending_tipo_pedido');
            const pendingRedirect = storageGet('pending_redirect');
            const authenticated = document.body.dataset.authenticated === '1';

            if (pending && authenticated) {
                await this.persistTipo(pending);
                storageRemove('pending_tipo_pedido');
                storageRemove('pending_redirect');
                window.location.replace(pendingRedirect || (pending === 'pickup' ? '/pickup' : '/express'));
                return;
            }

            this.abierto = force || !hasChoice;
        },

        async choose(tipo) {
            const destination = tipo === 'pickup' ? '/pickup' : '/express';
            const authenticated = document.body.dataset.authenticated === '1';

            if (authenticated) {
                await this.persistTipo(tipo);
                this.abierto = false;
                window.location.assign(destination);
                return;
            }

            storageSet('pending_tipo_pedido', tipo);
            storageSet('pending_redirect', destination);
            window.location.assign('/login');
        },

        async persistTipo(tipo) {
            storageSet('tipo_pedido', tipo);
        },
    }));

    Alpine.data('flashMessage', () => ({
        show: true,
        init() {
            window.setTimeout(() => {
                this.show = false;
            }, 2000);
        },
    }));

    Alpine.data('modalComponent', () => ({
        show: false,
        name: '',
        focusable: false,

        init() {
            this.name = this.$el.dataset.modalName || '';
            this.show = this.$el.dataset.modalShow === '1';
            this.focusable = this.$el.dataset.modalFocusable === '1';

            this.$watch('show', value => {
                document.body.classList.toggle('overflow-y-hidden', value);
                if (value && this.focusable) {
                    window.setTimeout(() => this.firstFocusable()?.focus(), 100);
                }
            });
        },

        focusables() {
            const selector = "a, button, input:not([type='hidden']), textarea, select, details, [tabindex]:not([tabindex='-1'])";
            return [...this.$el.querySelectorAll(selector)].filter(element => !element.hasAttribute('disabled'));
        },

        firstFocusable() {
            return this.focusables()[0];
        },

        lastFocusable() {
            return this.focusables().slice(-1)[0];
        },

        nextFocusable() {
            const focusables = this.focusables();
            const index = focusables.indexOf(document.activeElement);
            return focusables[(index + 1) % focusables.length] || this.firstFocusable();
        },

        previousFocusable() {
            const focusables = this.focusables();
            const index = focusables.indexOf(document.activeElement);
            return focusables[Math.max(0, index - 1)] || this.lastFocusable();
        },

        openIfMatches(event) {
            if (event.detail === this.name) this.show = true;
        },

        closeIfMatches(event) {
            if (event.detail === this.name) this.show = false;
        },

        close() {
            this.show = false;
        },

        focusNext() {
            this.nextFocusable()?.focus();
        },

        focusPrevious() {
            this.previousFocusable()?.focus();
        },
    }));
}
