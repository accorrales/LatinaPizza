export function gpsPayload(position) {
    return { latitude: position.coords.latitude, longitude: position.coords.longitude, accuracy: position.coords.accuracy, recorded_at: new Date(position.timestamp).toISOString() };
}

export function initDeliveryTracking() {
    const root = document.getElementById('delivery-tracking');
    if (!root) return;
    const field = name => root.querySelector(`[data-${name}]`);
    let orders = [], watch = null, position, sending = false, request, lastSent = 0, version = 0;
    const message = text => { field('message').textContent = text; };
    function stop(text = 'GPS detenido. La última señal dejará de mostrarse como reciente.') {
        version++;
        if (watch !== null) navigator.geolocation.clearWatch(watch);
        watch = null; position = null; request?.abort();
        field('order').disabled = false; field('stop').disabled = true;
        field('start').disabled = !field('order').value;
        message(text);
    }
    function address() {
        const order = orders.find(order => String(order.id) === field('order').value);
        field('address').textContent = order ? Object.values(order.direccion || {}).filter(Boolean).join('\n') : '';
        field('start').disabled = !order || watch !== null;
    }
    async function reload() {
        try {
            const response = await fetch(root.dataset.ordersUrl, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(10000) });
            if (!response.ok) {
                if ([401, 403, 419].includes(response.status)) stop('La sesión venció. Iniciá sesión nuevamente.');
                throw new Error('No se pudieron consultar las entregas.');
            }
            const payload = await response.json();
            orders = payload.data;
            const selected = field('order').value;
            field('order').replaceChildren(new Option(orders.length ? 'Seleccioná un pedido' : 'No hay entregas asignadas', ''));
            for (const order of orders) field('order').add(new Option(`Pedido #${order.id}`, order.id));
            if (orders.some(order => String(order.id) === selected)) field('order').value = selected;
            else if (watch !== null) stop('El pedido finalizó o ya no está asignado. GPS detenido.');
            address();
        } catch (error) { message(error.message); }
    }
    async function send() {
        if (watch === null || !position || sending || position.timestamp <= lastSent) return;
        if (Date.now() - position.timestamp > 110000) { message('Esperando una señal GPS más reciente…'); return; }
        const snapshot = position;
        const currentVersion = version;
        sending = true;
        request = new AbortController();
        const timeout = setTimeout(() => request?.abort(), 10000);
        try {
            const response = await fetch(`${root.dataset.locationBase}/${encodeURIComponent(field('order').value)}/ubicacion`, {
                method: 'POST', signal: request.signal, headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(gpsPayload(snapshot)),
            });
            if (version !== currentVersion) return;
            if (!response.ok) {
                if ([401, 403, 404, 409, 419].includes(response.status)) { stop('Envío detenido. Revisá la sesión, la asignación y el estado del pedido.'); reload(); return; }
                throw new Error(response.status === 429 ? 'Demasiados envíos. Esperando para reintentar…' : 'No se pudo enviar la ubicación. Reintentando…');
            }
            lastSent = snapshot.timestamp;
            message(`Ubicación enviada a las ${new Date().toLocaleTimeString('es-CR')}. Mantené esta página abierta.`);
        } catch (error) { if (version === currentVersion) message(error.name === 'AbortError' ? 'Sin conexión. Reintentando…' : error.message); }
        finally { clearTimeout(timeout); sending = false; }
    }
    field('order').addEventListener('change', address);
    field('start').addEventListener('click', () => {
        if (!field('order').value || watch !== null) return;
        if (!window.isSecureContext || !navigator.geolocation) { message('El GPS requiere HTTPS o localhost y un navegador compatible.'); return; }
        lastSent = 0;
        field('order').disabled = true; field('start').disabled = true; field('stop').disabled = false;
        message('Solicitando permiso de ubicación…');
        watch = navigator.geolocation.watchPosition(value => { position = value; }, error => {
            if (error.code === 1) stop('Permiso de ubicación denegado. Habilitalo para compartir GPS.');
            else message('No se pudo obtener GPS. Buscando una nueva señal…');
        }, { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 });
    });
    field('stop').addEventListener('click', () => stop());
    field('reload').addEventListener('click', reload);
    const timer = setInterval(send, 5000);
    const ordersTimer = setInterval(reload, 15000);
    window.addEventListener('pagehide', () => { stop(); clearInterval(timer); clearInterval(ordersTimer); });
    reload();
}
