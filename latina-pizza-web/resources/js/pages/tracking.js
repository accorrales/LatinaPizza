export const labels = { pendiente: 'Pendiente', pagado: 'Pagado', nuevo: 'Recibido', preparando: 'En preparación', preparacion: 'En preparación', listo: 'Listo para entrega', en_camino: 'En camino', entregado: 'Entregado', cancelado: 'Cancelado' };

export function stepsFor(order) {
    return order.tipo === 'pickup' ? ['Recibido', 'Preparación', 'Listo', 'Entregado'] : ['Recibido', 'Preparación', 'Listo', 'En camino', 'Entregado'];
}

export function progressFor(order) {
    if (order.estado === 'cancelado') return -1;
    if (order.estado === 'entregado') return stepsFor(order).length - 1;
    if (order.estado === 'en_camino') return 3;
    if (order.estado === 'listo' || order.cocina === 'listo') return 2;
    if (order.estado === 'preparando' || order.cocina === 'preparacion') return 1;
    return 0;
}

export function addressFor(order) {
    if (order.tipo === 'pickup') return `Retiro en sucursal: ${order.sucursal}`;
    const a = order.direccion || {};
    return [a.nombre, a.direccion_exacta, [a.distrito, a.canton, a.provincia].filter(Boolean).join(', '), a.referencias, a.telefono_contacto ? `Contacto: ${a.telefono_contacto}` : ''].filter(Boolean).join('\n') || 'Dirección no registrada.';
}

export function initTracking() {
    const root = document.getElementById('tracking-board');
    if (!root) return;
    const find = (selector) => root.querySelector(selector);
    const form = find('[data-filters]');
    const list = find('[data-orders]');
    const error = find('[data-error]');
    let page = 1, lastPage = 1, timer, controller, sequence = 0, stopped = false;
    let filters = new URLSearchParams(new FormData(form));
    const date = (value) => {
        if (!value) return 'Sin registrar';
        const parsed = new Date(value);
        return Number.isNaN(parsed.getTime()) ? 'Sin registrar' : parsed.toLocaleString('es-CR');
    };
    const money = new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' });

    function render(orders) {
        const expanded = new Set([...list.querySelectorAll('article')].filter((card) => card.querySelector('details').open).map((card) => card.dataset.order));
        const fragment = document.createDocumentFragment();
        for (const order of orders) {
            const card = find('[data-card]').content.firstElementChild.cloneNode(true);
            card.dataset.order = String(order.id);
            const text = (selector, value) => { card.querySelector(selector).textContent = value; };
            text('[data-id]', `Pedido #${order.id}`);
            text('[data-state]', labels[order.estado] || order.estado);
            text('[data-client]', order.cliente);
            text('[data-branch]', `${order.sucursal} · ${order.tipo === 'express' ? 'Express' : order.tipo === 'pickup' ? 'Pickup' : 'Sin método registrado'}`);
            text('[data-created]', date(order.created_at));
            text('[data-total]', money.format(order.total));
            text('[data-promised]', date(order.promised_at));
            text('[data-ready]', date(order.ready_at));
            text('[data-address]', addressFor(order));
            const progress = progressFor(order);
            card.querySelector('[data-progress]').className = order.tipo === 'pickup' ? 'grid grid-cols-4 gap-2 text-center text-xs' : 'grid grid-cols-5 gap-2 text-center text-xs';
            stepsFor(order).forEach((label, index) => {
                const step = document.createElement('li');
                step.className = index <= progress ? 'rounded-xl bg-blue-50 p-2 font-bold text-blue-700' : 'rounded-xl bg-slate-50 p-2 text-slate-400';
                step.textContent = `${index <= progress ? '✓' : index + 1} ${label}`;
                if (index === progress) step.setAttribute('aria-current', 'step');
                card.querySelector('[data-progress]').append(step);
            });
            for (const event of order.historial || []) {
                const item = document.createElement('li');
                item.textContent = `${labels[event.estado] || event.estado} · ${date(event.fecha)}`;
                card.querySelector('[data-history]').append(item);
            }
            if (!order.historial?.length) text('[data-history]', 'Sin eventos registrados.');
            card.querySelector('[data-details]').open = expanded.has(String(order.id));
            card.querySelector('[data-live-link]').href = `${root.dataset.liveBase}/${encodeURIComponent(order.id)}/rastreo`;
            const link = card.querySelector('[data-admin-link]');
            if (link) link.href = `${root.dataset.adminBase}/${encodeURIComponent(order.id)}`;
            fragment.append(card);
        }
        list.replaceChildren(fragment);
    }

    async function refresh() {
        clearTimeout(timer);
        controller?.abort();
        controller = new AbortController();
        const activeController = controller;
        const current = ++sequence;
        const timeout = setTimeout(() => activeController.abort(), 12000);
        find('[data-refresh]').disabled = true;
        list.setAttribute('aria-busy', 'true');
        const query = new URLSearchParams(filters);
        query.set('page', page);
        try {
            const response = await fetch(`${root.dataset.endpoint}?${query}`, { signal: activeController.signal, credentials: 'same-origin', headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (current !== sequence) return;
            if (!response.ok) {
                if ([401, 403, 419].includes(response.status)) {
                    stopped = true;
                    list.replaceChildren();
                    find('[data-empty]').hidden = true;
                    find('[data-summary]').textContent = 'Acceso no disponible';
                    throw new Error('La sesión venció o no tenés acceso. Iniciá sesión con una cuenta del personal.');
                }
                throw new Error('No se pudo actualizar. Revisá la conexión e intentá de nuevo.');
            }
            const payload = await response.json();
            if (current !== sequence) return;
            if (!Array.isArray(payload.data) || !payload.meta) throw new Error('Respuesta no válida del servidor.');
            lastPage = payload.meta.last_page;
            if (page > lastPage) { page = Math.max(1, lastPage); clearTimeout(timeout); return refresh(); }
            render(payload.data);
            find('[data-empty]').hidden = payload.data.length !== 0;
            find('[data-summary]').textContent = `${payload.meta.total} pedidos encontrados`;
            find('[data-updated]').textContent = `Actualizado: ${date(payload.meta.server_time)}`;
            find('[data-page]').textContent = `Página ${page} de ${lastPage}`;
            error.hidden = true;
        } catch (failure) {
            if (current !== sequence) return;
            error.textContent = failure.name === 'AbortError' ? 'La actualización tardó demasiado. Intentá de nuevo.' : failure.message;
            error.hidden = false;
            if (!list.children.length && !stopped) find('[data-summary]').textContent = 'No se pudieron cargar los pedidos';
            find('[data-updated]').textContent = 'Sin sincronizar · los datos visibles pueden estar desactualizados';
        } finally {
            clearTimeout(timeout);
            if (current === sequence) {
                list.setAttribute('aria-busy', 'false');
                find('[data-refresh]').disabled = stopped;
                find('[data-prev]').disabled = stopped || page <= 1;
                find('[data-next]').disabled = stopped || page >= lastPage;
                if (!stopped && !document.hidden) timer = setTimeout(refresh, 15000);
            }
        }
    }
    form.addEventListener('submit', (event) => { event.preventDefault(); if (stopped) return; filters = new URLSearchParams(new FormData(form)); page = 1; refresh(); });
    find('[data-refresh]').addEventListener('click', refresh);
    find('[data-prev]').addEventListener('click', () => { if (page > 1) { page--; refresh(); } });
    find('[data-next]').addEventListener('click', () => { if (page < lastPage) { page++; refresh(); } });
    document.addEventListener('visibilitychange', () => { clearTimeout(timer); if (!document.hidden && !stopped) refresh(); });
    window.addEventListener('pagehide', () => { clearTimeout(timer); controller?.abort(); });
    refresh();
}
