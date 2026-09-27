export function gpsPayload(position) {
    return { latitude: position.coords.latitude, longitude: position.coords.longitude, accuracy: position.coords.accuracy, recorded_at: new Date(position.timestamp).toISOString() };
}

export function wazeUrl(stop) {
    if (!stop) return '#';
    return `https://www.waze.com/ul?ll=${encodeURIComponent(`${stop.latitude},${stop.longitude}`)}&navigate=yes&utm_source=latina_pizza`;
}

export function googleRouteUrl(plan) {
    const stops = plan?.stops || [];
    if (!stops.length) return '#';
    const params = new URLSearchParams({ api: '1', travelmode: 'driving', dir_action: 'navigate' });
    if (plan?.origin) params.set('origin', `${plan.origin.latitude},${plan.origin.longitude}`);
    const last = stops[stops.length - 1];
    params.set('destination', `${last.latitude},${last.longitude}`);
    if (stops.length > 1) params.set('waypoints', stops.slice(0, -1).map(stop => `${stop.latitude},${stop.longitude}`).join('|'));
    return `https://www.google.com/maps/dir/?${params.toString()}`;
}

function formatDuration(seconds) {
    const minutes = Math.max(0, Math.round(Number(seconds || 0) / 60));
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;
    return rest ? `${hours} h ${rest} min` : `${hours} h`;
}

function formatDistance(meters) {
    const value = Number(meters || 0);
    return value < 1000 ? `${Math.round(value)} m` : `${(value / 1000).toFixed(1)} km`;
}

function addressText(address = {}) {
    return [address.nombre, address.direccion_exacta, [address.distrito, address.canton, address.provincia].filter(Boolean).join(', '), address.referencias]
        .filter(Boolean).join(' · ') || 'Dirección registrada';
}

function activeCount(plan) {
    return Number(plan?.order_count || 0) + (plan?.missing_coordinates?.length || 0);
}

export function initDeliveryTracking() {
    const root = document.getElementById('delivery-tracking');
    if (!root) return;
    const field = name => root.querySelector(`[data-${name}]`);
    let plan = null, map = null, watch = null, position = null, sending = false, request = null;
    let lastSent = 0, version = 0, routeController = null, lastRouteRefresh = 0;

    const message = text => { field('message').textContent = text; };

    function stop(text = 'GPS detenido. La última señal dejará de mostrarse como reciente.') {
        version++;
        if (watch !== null) navigator.geolocation.clearWatch(watch);
        watch = null;
        position = null;
        request?.abort();
        field('stop').disabled = true;
        field('start').disabled = activeCount(plan) === 0;
        message(text);
    }

    function renderStops() {
        const list = field('stops');
        list.replaceChildren();
        const stops = plan?.stops || [];
        field('empty').hidden = stops.length > 0 || (plan?.missing_coordinates?.length || 0) > 0;
        for (const stop of stops) {
            const card = document.createElement('article');
            card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm';

            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-4';
            const title = document.createElement('div');
            const eyebrow = document.createElement('p');
            eyebrow.className = 'text-xs font-bold uppercase tracking-widest text-blue-600';
            eyebrow.textContent = `Parada ${stop.sequence}`;
            const heading = document.createElement('h3');
            heading.className = 'mt-1 text-lg font-bold text-slate-900';
            heading.textContent = `Pedido #${stop.order_id}`;
            const eta = document.createElement('p');
            eta.className = 'text-right text-sm font-bold text-slate-900';
            eta.textContent = new Date(stop.eta_at).toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
            const etaSub = document.createElement('span');
            etaSub.className = 'block text-xs font-normal text-slate-500';
            etaSub.textContent = `en ~${formatDuration(stop.eta_seconds)}`;
            eta.append(etaSub);
            title.append(eyebrow, heading);
            top.append(title, eta);

            const address = document.createElement('p');
            address.className = 'mt-3 text-sm text-slate-600';
            address.textContent = addressText(stop.address);

            const metrics = document.createElement('p');
            metrics.className = 'mt-2 text-xs text-slate-500';
            metrics.textContent = `${formatDistance(stop.leg_distance_meters)} desde la parada anterior · ${formatDuration(stop.leg_seconds)} conduciendo · ${formatDuration(stop.service_seconds)} estimados para entregar`;

            const nav = document.createElement('a');
            nav.className = 'mt-4 inline-flex rounded-xl bg-[#071426] px-4 py-2 text-sm font-bold text-white';
            nav.href = wazeUrl(stop);
            nav.target = '_blank';
            nav.rel = 'noopener noreferrer';
            nav.textContent = 'Abrir en Waze';
            card.append(top, address, metrics, nav);
            list.append(card);
        }

        if (plan?.missing_coordinates?.length) {
            const warning = document.createElement('div');
            warning.className = 'rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900';
            warning.textContent = `Faltan coordenadas en ${plan.missing_coordinates.length} pedido(s): ${plan.missing_coordinates.map(id => `#${id}`).join(', ')}. El GPS se comparte, pero no se pueden incluir en la optimización hasta corregir su dirección.`;
            list.append(warning);
        }
    }

    async function renderPlan() {
        const count = activeCount(plan);
        const validStops = plan?.stops?.length || 0;
        field('summary').textContent = count ? `${count} entrega${count === 1 ? '' : 's'} activa${count === 1 ? '' : 's'}` : 'Sin entregas activas';
        field('total-time').textContent = validStops ? formatDuration(plan.total_seconds) : '—';
        field('distance').textContent = validStops ? formatDistance(plan.distance_meters) : '—';
        field('provider').textContent = validStops
            ? (plan.approximate ? 'Estimación aproximada' : 'Ruta optimizada por carretera')
            : 'Esperando pedidos';
        field('route-note').textContent = validStops
            ? `${plan.approximate ? 'El proveedor de carretera no respondió; se usa un cálculo de respaldo. ' : ''}Los ETA incluyen tiempo estimado de entrega por parada y no incluyen tráfico en tiempo real.`
            : 'Cuando te asignen pedidos express aparecerán aquí automáticamente.';

        const next = plan?.stops?.[0];
        field('next-card').hidden = !next;
        if (next) {
            field('next-order').textContent = `Pedido #${next.order_id}`;
            field('next-address').textContent = addressText(next.address);
            field('next-eta').textContent = `Llegada estimada ${new Date(next.eta_at).toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' })} · ~${formatDuration(next.eta_seconds)}`;
            field('waze').href = wazeUrl(next);
        }

        const fullRoute = field('full-route');
        fullRoute.hidden = !next;
        if (next) fullRoute.href = googleRouteUrl(plan);
        field('start').disabled = watch !== null || count === 0;
        renderStops();

        if ((plan?.origin || validStops) && !map) {
            const [module] = await Promise.all([import('maplibre-gl'), import('maplibre-gl/dist/maplibre-gl.css')]);
            map = (await import('./delivery-route-map')).createDeliveryRouteMap(field('map'), module.default || module, () => {
                field('map-error').hidden = false;
            });
        }
        if (map) {
            const live = position ? gpsPayload(position) : null;
            map.update(plan, live);
        }
    }

    async function reload() {
        routeController?.abort();
        routeController = new AbortController();
        const timeout = setTimeout(() => routeController.abort(), 12000);
        field('reload').disabled = true;
        try {
            const response = await fetch(root.dataset.routeUrl, {
                headers: { Accept: 'application/json' }, cache: 'no-store', signal: routeController.signal,
            });
            if (!response.ok) {
                if ([401, 403, 419].includes(response.status)) stop('La sesión venció. Iniciá sesión nuevamente.');
                throw new Error('No se pudo calcular la ruta de entregas.');
            }
            const payload = await response.json();
            plan = payload.data;
            lastRouteRefresh = Date.now();
            await renderPlan();
            if (activeCount(plan) === 0 && watch !== null) stop('No quedan entregas activas. GPS detenido.');
        } catch (error) {
            if (error.name !== 'AbortError') message(error.message);
        } finally {
            clearTimeout(timeout);
            field('reload').disabled = false;
        }
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
            const response = await fetch(root.dataset.locationUrl, {
                method: 'POST', signal: request.signal,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(gpsPayload(snapshot)),
            });
            if (version !== currentVersion) return;
            if (!response.ok) {
                if ([401, 403, 404, 409, 419].includes(response.status)) { stop('Envío detenido. Revisá la sesión, la asignación y el estado de tus pedidos.'); await reload(); return; }
                throw new Error(response.status === 429 ? 'Demasiados envíos. Esperando para reintentar…' : 'No se pudo enviar la ubicación. Reintentando…');
            }
            lastSent = snapshot.timestamp;
            const payload = await response.json();
            const shared = payload.data?.order_ids?.length || activeCount(plan);
            message(`Ubicación compartida con ${shared} pedido${shared === 1 ? '' : 's'} · ${new Date().toLocaleTimeString('es-CR')}.`);
            if (Date.now() - lastRouteRefresh > 7000) await reload();
            else if (map) map.update(plan, gpsPayload(snapshot));
        } catch (error) {
            if (version === currentVersion) message(error.name === 'AbortError' ? 'Sin conexión. Reintentando…' : error.message);
        } finally {
            clearTimeout(timeout);
            sending = false;
        }
    }

    field('start').addEventListener('click', () => {
        if (activeCount(plan) === 0 || watch !== null) return;
        if (!window.isSecureContext || !navigator.geolocation) { message('El GPS requiere HTTPS o localhost y un navegador compatible.'); return; }
        lastSent = 0;
        field('start').disabled = true;
        field('stop').disabled = false;
        message('Solicitando permiso de ubicación…');
        watch = navigator.geolocation.watchPosition(value => {
            position = value;
            if (map) map.update(plan, gpsPayload(value));
        }, error => {
            if (error.code === 1) stop('Permiso de ubicación denegado. Habilitalo para compartir GPS.');
            else message('No se pudo obtener GPS. Buscando una nueva señal…');
        }, { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 });
    });

    field('stop').addEventListener('click', () => stop());
    field('reload').addEventListener('click', reload);
    const sendTimer = setInterval(send, 5000);
    const routeTimer = setInterval(reload, 15000);
    window.addEventListener('pagehide', () => {
        stop();
        clearInterval(sendTimer);
        clearInterval(routeTimer);
        routeController?.abort();
        map?.destroy();
    });
    reload();
}
