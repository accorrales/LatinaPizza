const baseStyle = {
    version: 8,
    sources: {
        osm: {
            type: 'raster',
            tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
            tileSize: 256,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        },
    },
    layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
};

function validPoint(point) {
    return point && Number.isFinite(Number(point.latitude)) && Math.abs(Number(point.latitude)) <= 90
        && Number.isFinite(Number(point.longitude)) && Math.abs(Number(point.longitude)) <= 180;
}

function emptyGeometry() {
    return { type: 'Feature', geometry: { type: 'LineString', coordinates: [] }, properties: {} };
}

function featureFor(geometry) {
    if (!geometry || geometry.type !== 'LineString' || !Array.isArray(geometry.coordinates)) return emptyGeometry();
    return { type: 'Feature', geometry, properties: {} };
}

function stopElement(sequence) {
    const element = document.createElement('div');
    element.textContent = String(sequence);
    element.setAttribute('aria-label', `Parada ${sequence}`);
    Object.assign(element.style, {
        width: '30px', height: '30px', borderRadius: '9999px', display: 'grid', placeItems: 'center',
        background: '#dc2626', color: '#fff', fontWeight: '800', border: '3px solid #fff',
        boxShadow: '0 4px 12px rgba(15,23,42,.28)', fontSize: '12px',
    });
    return element;
}

export function createDeliveryRouteMap(container, maplibre, onError = () => {}) {
    const map = new maplibre.Map({ container, style: baseStyle, center: [-84.38, 10.1], zoom: 12 });
    map.addControl(new maplibre.NavigationControl(), 'top-right');
    map.on('error', onError);

    let loaded = false;
    let pending = null;
    let driverMarker = null;
    let stopMarkers = [];
    let fittedKey = '';

    const render = ({ plan, location }) => {
        if (!loaded) {
            pending = { plan, location };
            return;
        }

        const source = map.getSource('delivery-route');
        source?.setData(featureFor(plan?.geometry));

        if (validPoint(location)) {
            const coords = [Number(location.longitude), Number(location.latitude)];
            if (!driverMarker) driverMarker = new maplibre.Marker({ color: '#2563eb' }).setLngLat(coords).addTo(map);
            else driverMarker.setLngLat(coords);
        } else if (validPoint(plan?.origin)) {
            const coords = [Number(plan.origin.longitude), Number(plan.origin.latitude)];
            if (!driverMarker) driverMarker = new maplibre.Marker({ color: '#2563eb' }).setLngLat(coords).addTo(map);
            else driverMarker.setLngLat(coords);
        }

        stopMarkers.forEach(marker => marker.remove());
        stopMarkers = [];
        for (const stop of plan?.stops || []) {
            if (!validPoint(stop)) continue;
            stopMarkers.push(new maplibre.Marker({ element: stopElement(stop.sequence) })
                .setLngLat([Number(stop.longitude), Number(stop.latitude)]).addTo(map));
        }

        const fitKey = (plan?.stops || []).map(stop => `${stop.order_id}:${stop.sequence}`).join('|');
        if (fitKey && fitKey !== fittedKey) {
            const bounds = new maplibre.LngLatBounds();
            const start = validPoint(location) ? location : plan?.origin;
            if (validPoint(start)) bounds.extend([Number(start.longitude), Number(start.latitude)]);
            for (const stop of plan.stops || []) {
                if (validPoint(stop)) bounds.extend([Number(stop.longitude), Number(stop.latitude)]);
            }
            if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 55, maxZoom: 15, duration: 450 });
            fittedKey = fitKey;
        }
        map.resize();
    };

    map.on('load', () => {
        loaded = true;
        map.addSource('delivery-route', { type: 'geojson', data: emptyGeometry() });
        map.addLayer({
            id: 'delivery-route-line', type: 'line', source: 'delivery-route',
            paint: { 'line-color': '#2563eb', 'line-width': 5, 'line-opacity': 0.82 },
            layout: { 'line-cap': 'round', 'line-join': 'round' },
        });
        if (pending) {
            const value = pending;
            pending = null;
            render(value);
        }
    });

    return {
        update(plan, location = null) { render({ plan, location }); },
        destroy() {
            driverMarker?.remove();
            stopMarkers.forEach(marker => marker.remove());
            map.remove();
        },
    };
}
