function parseJson(value, fallback) {
    try {
        return JSON.parse(value || '');
    } catch {
        return fallback;
    }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    })[character]);
}

function normalizePair(lat, lng) {
    const toNumber = value => (value === null || value === undefined || value === '') ? null : Number(value);
    let normalizedLat = toNumber(lat);
    let normalizedLng = toNumber(lng);
    if (normalizedLat === null || normalizedLng === null) return [null, null];

    if (normalizedLng > 180) normalizedLng -= 360;
    const looksSwapped = (Math.abs(normalizedLat) > 90 && Math.abs(normalizedLng) <= 90)
        || (Math.abs(normalizedLng) < 20 && Math.abs(normalizedLat) > 20);
    if (looksSwapped) [normalizedLat, normalizedLng] = [normalizedLng, normalizedLat];

    normalizedLat = Math.max(-90, Math.min(90, normalizedLat));
    normalizedLng = Math.max(-180, Math.min(180, normalizedLng));
    return [normalizedLat, normalizedLng];
}

export async function initExpressMap() {
    const element = document.getElementById('map');
    if (!element) return;

    const apiKey = element.dataset.maptilerKey || '';
    if (!apiKey) {
        element.textContent = 'Falta MAPTILER_KEY en el frontend (.env)';
        element.classList.add('p-4', 'text-red-700', 'bg-red-50', 'rounded-xl');
        return;
    }

    const [{ default: maplibregl }, { default: circle }] = await Promise.all([
        import('maplibre-gl'),
        import('@turf/circle'),
        import('maplibre-gl/dist/maplibre-gl.css'),
    ]);

    const address = parseJson(element.dataset.address, {});
    const branches = parseJson(element.dataset.branches, []);
    const maxKm = Number(element.dataset.maxKm || 10) || 10;
    const currency = element.dataset.currency || '₡';

    window.__latinaExpressMap?.remove?.();

    const map = new maplibregl.Map({
        container: element,
        style: `https://api.maptiler.com/maps/streets-v2/style.json?key=${encodeURIComponent(apiKey)}`,
        center: [-84.09, 9.936],
        zoom: 12,
        attributionControl: true,
    });
    window.__latinaExpressMap = map;

    map.addControl(new maplibregl.NavigationControl({ visualizePitch: true }), 'top-right');
    const hasCoordinates = item => normalizePair(item?.latitud, item?.longitud).every(value => value !== null);
    const bounds = new maplibregl.LngLatBounds();

    const markerHtml = (title, addressText, extras = '') => {
        const extraLine = extras ? `<br>${escapeHtml(extras)}` : '';
        return `<strong>${escapeHtml(title)}</strong><br>${escapeHtml(addressText)}${extraLine}`;
    };

    const addMarker = (lat, lng, html, color = '#d11') => {
        const markerElement = document.createElement('div');
        markerElement.style.width = '24px';
        markerElement.style.height = '24px';
        markerElement.style.borderRadius = '50%';
        markerElement.style.background = color;
        markerElement.style.boxShadow = '0 0 0 2px #fff';

        new maplibregl.Marker({ element: markerElement, anchor: 'bottom' })
            .setLngLat([lng, lat])
            .setPopup(new maplibregl.Popup({ offset: 18 }).setHTML(html))
            .addTo(map);
        bounds.extend([lng, lat]);
    };

    map.on('load', () => {
        if (hasCoordinates(address)) {
            const [lat, lng] = normalizePair(address.latitud, address.longitud);
            addMarker(lat, lng, markerHtml(address.nombre ?? 'Dirección', address.direccion_exacta ?? ''), '#2b6cb0');
        }

        const validBranches = (Array.isArray(branches) ? branches : []).filter(hasCoordinates);
        validBranches.forEach(branch => {
            const [lat, lng] = normalizePair(branch.latitud, branch.longitud);
            const distance = branch.distancia_km !== null && branch.distancia_km !== undefined ? `${branch.distancia_km} km` : '';
            const fee = branch.delivery_fee !== null && branch.delivery_fee !== undefined ? ` • Delivery: ${currency}${branch.delivery_fee}` : '';
            const coverage = branch.covered === false ? ' • Fuera de cobertura' : '';
            addMarker(lat, lng, markerHtml(branch.nombre ?? `Sucursal #${branch.id}`, branch.direccion ?? '', `${distance}${fee}${coverage}`));
        });

        if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 40, maxZoom: 16, duration: 500 });

        const sourceId = 'coverage-source';
        const layerId = 'coverage-layer';
        const drawCoverage = (lat, lng) => {
            const [normalizedLat, normalizedLng] = normalizePair(lat, lng);
            if (normalizedLat === null || normalizedLng === null) return;
            const geometry = circle([normalizedLng, normalizedLat], maxKm, { steps: 64, units: 'kilometers' });
            if (map.getLayer(layerId)) map.removeLayer(layerId);
            if (map.getSource(sourceId)) map.removeSource(sourceId);
            map.addSource(sourceId, { type: 'geojson', data: geometry });
            map.addLayer({
                id: layerId,
                type: 'fill',
                source: sourceId,
                paint: { 'fill-color': '#ef4444', 'fill-opacity': 0.12 },
            });
        };

        const selectBranch = branch => {
            if (!branch) return;
            const [lat, lng] = normalizePair(branch.latitud, branch.longitud);
            drawCoverage(lat, lng);
        };

        const checked = document.querySelector('input[name="sucursal_id"]:checked');
        if (checked) {
            selectBranch(validBranches.find(branch => Number(branch.id) === Number(checked.value)));
        } else {
            const closest = validBranches
                .filter(branch => branch.covered)
                .sort((a, b) => (a.distancia_km ?? 9999) - (b.distancia_km ?? 9999))[0];
            if (closest) {
                selectBranch(closest);
                const radio = document.querySelector(`input[name="sucursal_id"][value="${Number(closest.id)}"]`);
                if (radio && !radio.disabled) radio.checked = true;
            }
        }

        document.querySelectorAll('input[name="sucursal_id"]').forEach(radio => {
            radio.addEventListener('change', () => {
                selectBranch(validBranches.find(branch => Number(branch.id) === Number(radio.value)));
            });
        });

        if (window.ResizeObserver) {
            new ResizeObserver(() => map.resize()).observe(element);
        } else {
            window.addEventListener('resize', () => map.resize());
        }
    });
}
