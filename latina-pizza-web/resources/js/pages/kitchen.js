function csrfHeaders(json = true) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    };
    if (json) headers['Content-Type'] = 'application/json';
    return headers;
}

async function responseError(response) {
    let message = `HTTP ${response.status}`;
    try {
        const payload = await response.json();
        if (payload?.message) message = payload.message;
    } catch {
        // Keep the generic HTTP message.
    }
    return new Error(message);
}

export function registerKitchenComponent(Alpine) {
    Alpine.data('kitchenPanel', () => ({
        orders: [],
        counts: { nuevo: 0, preparacion: 0, listo: 0 },
        meta: { server_time: '', pagination: { current_page: 1, per_page: 50, total: 0, last_page: 1 } },
        filters: { status: 'nuevo', tipo_pedido: '', search: '', limit: 50 },
        timer: null,
        loading: false,
        errMsg: '',
        apiBase: '',

        async init() {
            this.apiBase = this.$el.dataset.apiUrl || '/kitchen/api';
            await this.fetchOrders(true);
            this.timer = window.setInterval(() => this.fetchOrders(), 6000);
            window.addEventListener('beforeunload', () => window.clearInterval(this.timer), { once: true });
        },

        tabBtn(status) {
            return `px-3 py-1 text-sm rounded ${this.filters.status === status ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'}`;
        },

        setStatus(status) {
            this.filters.status = status;
            this.fetchOrders(true);
        },

        nextLabel(status) {
            return status === 'nuevo' ? '→ Preparación' : (status === 'preparacion' ? '→ Listo' : 'Listo');
        },

        async getJson(path) {
            const response = await fetch(`${this.apiBase}${path}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: csrfHeaders(false),
            });
            if (!response.ok) throw await responseError(response);
            return response.json();
        },

        async patchJson(path, body = {}) {
            const response = await fetch(`${this.apiBase}${path}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: csrfHeaders(true),
                body: JSON.stringify(body),
            });
            if (!response.ok) throw await responseError(response);
            return response.json();
        },

        async fetchOrders(reset = false) {
            this.loading = true;
            this.errMsg = '';
            try {
                const query = new URLSearchParams({
                    status: this.filters.status,
                    limit: String(this.filters.limit || 50),
                });
                if (this.filters.tipo_pedido) query.set('tipo_pedido', this.filters.tipo_pedido);
                if (this.filters.search) query.set('search', this.filters.search);

                const payload = await this.getJson(`/orders?${query}`);
                this.orders = payload.data || [];
                this.counts = payload.meta?.counts || this.counts;
                this.meta = payload.meta || this.meta;

                if (reset) window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                console.error(error);
                this.errMsg = error.message || 'Error al cargar pedidos';
            } finally {
                this.loading = false;
            }
        },

        async updateStatus(order, status) {
            try {
                await this.patchJson(`/orders/${order.id}/status`, { status });
                await this.fetchOrders();
            } catch (error) {
                this.errMsg = error.message || 'No se pudo actualizar el estado';
            }
        },

        async advance(order) {
            await this.updateStatus(order, order.kitchen_status === 'nuevo' ? 'preparacion' : 'listo');
        },

        async markReady(order) {
            try {
                await this.patchJson(`/orders/${order.id}/ready`);
                await this.fetchOrders();
            } catch (error) {
                this.errMsg = error.message || 'No se pudo marcar el pedido como listo';
            }
        },

        async togglePriority(order) {
            try {
                await this.patchJson(`/orders/${order.id}/priority`, { priority: !order.priority });
                await this.fetchOrders();
            } catch (error) {
                this.errMsg = error.message || 'No se pudo cambiar la prioridad';
            }
        },

        async updateSla(order, minutes) {
            const parsed = Number.parseInt(minutes, 10);
            if (Number.isNaN(parsed) || parsed < 5 || parsed > 240) return;
            try {
                await this.patchJson(`/orders/${order.id}/sla`, { sla_minutes: parsed });
                await this.fetchOrders();
            } catch (error) {
                this.errMsg = error.message || 'No se pudo actualizar el SLA';
            }
        },

        async updateNotes(order, notes) {
            try {
                await this.patchJson(`/orders/${order.id}/notes`, { notes });
                const target = this.orders.find(item => item.id === order.id);
                if (target) target.notas = notes;
            } catch (error) {
                this.errMsg = error.message || 'No se pudo guardar la nota';
            }
        },
    }));
}
