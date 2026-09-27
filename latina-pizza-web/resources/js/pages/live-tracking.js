import { labels } from './tracking';
import { createLiveMap, validLocation, isStale } from './live-map';

function durationText(seconds) {
    const minutes = Math.max(0, Math.round(Number(seconds || 0) / 60));
    if (minutes < 60) return `${minutes} min`;
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;
    return rest ? `${hours} h ${rest} min` : `${hours} h`;
}

export function initLiveTracking() {
    const root = document.getElementById('live-tracking');
    if (!root) return;
    const field = name => root.querySelector(`[data-${name}]`);
    let map, timer, controller, generation = 0, closed = false, lastLocation;
    const removeMap = () => { map?.destroy(); map = null; lastLocation = null; field('map').hidden = true; };
    const staleNotice = () => {
        if (lastLocation) field('message').textContent = isStale(lastLocation)
            ? 'Señal GPS desactualizada. Se muestra la última posición conocida.'
            : 'Ubicación reciente del repartidor. Actualización cada 5 segundos.';
    };
    const freshness = setInterval(staleNotice, 5000);

    function renderEta(route) {
        if (!route?.eta_at) {
            field('eta-card').hidden = true;
            return;
        }
        field('eta-card').hidden = false;
        field('eta').textContent = new Date(route.eta_at).toLocaleTimeString('es-CR', { hour: '2-digit', minute: '2-digit' });
        field('eta-duration').textContent = `Aproximadamente ${durationText(route.eta_seconds)} desde ahora`;
        const before = Number(route.stops_before || 0);
        field('queue').textContent = before === 0
            ? 'Tu pedido es la siguiente entrega de esta ruta.'
            : `Hay ${before} entrega${before === 1 ? '' : 's'} antes de tu pedido.`;
        field('estimate-note').textContent = `${route.approximate ? 'Estimación aproximada. ' : ''}El cálculo considera el orden de las entregas y el tiempo estimado en cada parada; no incluye tráfico en tiempo real.`;
    }

    async function refresh() {
        if (closed) return;
        clearTimeout(timer);
        controller?.abort();
        const request = controller = new AbortController();
        const version = ++generation;
        const timeout = setTimeout(() => request.abort(), 10000);
        field('refresh').disabled = true;
        try {
            const response = await fetch(root.dataset.endpoint, { signal: request.signal, cache: 'no-store', headers: { Accept: 'application/json' } });
            if (version !== generation) return;
            if (!response.ok) {
                if ([401, 403, 404, 419].includes(response.status)) { closed = true; removeMap(); }
                throw new Error(response.status === 403 ? 'No tenés acceso a este pedido.' : 'No se pudo consultar el pedido. Revisá tu sesión o conexión.');
            }
            const { data } = await response.json();
            if (version !== generation) return;
            field('status').textContent = labels[data.estado] || data.estado;
            field('error').hidden = true;
            renderEta(data.route);
            if (data.estado !== 'en_camino') {
                removeMap();
                field('updated').textContent = '';
                field('message').textContent = ['entregado', 'cancelado'].includes(data.estado) ? 'El seguimiento de esta entrega finalizó.' : data.tipo === 'pickup' ? 'Este pedido se retira en sucursal.' : 'El mapa estará disponible cuando tu pedido esté en camino.';
                closed = ['entregado', 'cancelado'].includes(data.estado) || data.tipo === 'pickup';
                return;
            }
            if (!validLocation(data.location)) {
                removeMap();
                field('message').textContent = 'El pedido está en camino. Esperando la primera señal GPS del repartidor…';
                field('updated').textContent = '';
                return;
            }
            if (!map) {
                const [module] = await Promise.all([import('maplibre-gl'), import('maplibre-gl/dist/maplibre-gl.css')]);
                if (version !== generation || closed) return;
                field('map').hidden = false;
                map = createLiveMap(field('map'), module.default || module, () => {
                    field('error').textContent = 'No se pudo cargar el fondo del mapa. Revisá la conexión.';
                    field('error').hidden = false;
                });
            }
            map.update(data.location, data.route?.destination || null);
            lastLocation = data.location;
            staleNotice();
            field('updated').textContent = `Última señal: ${new Date(data.location.recorded_at).toLocaleString('es-CR')}${data.location.accuracy == null ? '' : ` · Precisión aproximada: ${Math.round(data.location.accuracy)} m`}`;
        } catch (error) {
            if (version !== generation) return;
            field('error').textContent = error.name === 'AbortError' ? 'La conexión tardó demasiado. Reintentando…' : error.message;
            field('error').hidden = false;
        } finally {
            clearTimeout(timeout);
            if (version === generation) {
                field('refresh').disabled = closed;
                if (!closed && !document.hidden) timer = setTimeout(refresh, 5000);
            }
        }
    }
    field('refresh').addEventListener('click', refresh);
    document.addEventListener('visibilitychange', () => { clearTimeout(timer); if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => { closed = true; generation++; clearTimeout(timer); clearInterval(freshness); controller?.abort(); removeMap(); });
    refresh();
}
