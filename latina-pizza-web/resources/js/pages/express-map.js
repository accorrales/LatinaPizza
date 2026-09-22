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

function fallbackStyle() {
    return {
        version: 8,
        sources: {
            osm: {
                type: 'raster',
                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                tileSize: 256,
                attribution: '&copy; OpenStreetMap contributors',
            },
        },
        layers: [
            {
                id: 'osm-base',
                type: 'raster',
                source: 'osm',
                paint: {
                    'raster-saturation': -0.35,
                    'raster-contrast': 0.08,
                    'raster-brightness-min': 0.08,
                    'raster-brightness-max': 0.96,
                },
            },
        ],
    };
}

function buildMarkerElement(type = 'branch') {
    const wrapper = document.createElement('button');
    wrapper.type = 'button';
    wrapper.setAttribute('aria-label', type === 'address' ? 'Dirección de entrega' : 'Sucursal Latina Pizza');
    wrapper.className = 'latina-map-marker';
    wrapper.dataset.markerType = type;
    wrapper.style.cssText = [
        'width:42px',
        'height:42px',
        'border-radius:14px',
        'border:3px solid #fff',
        'display:grid',
        'place-items:center',
        'cursor:pointer',
        'box-shadow:0 10px 24px rgba(15,23,42,.22)',
        'transition:transform .2s ease, box-shadow .2s ease, background .2s ease',
        `background:${type === 'address' ? '#0f172a' : '#dc2626'}`,
        'color:#fff',
    ].join(';');

    wrapper.innerHTML = type === 'address'
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s6-5.15 6-11a6 6 0 1 0-12 0c0 5.85 6 11 6 11Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.2" fill="currentColor"/></svg>'
        : '<svg width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10h16l-1.4 9H5.4L4 10Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M7 10V7.8A4.8 4.8 0 0 1 11.8 3h.4A4.8 4.8 0 0 1 17 7.8V10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 14h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';

    wrapper.addEventListener('mouseenter', () => {
        wrapper.style.transform = 'translateY(-2px) scale(1.04)';
        wrapper.style.boxShadow = '0 14px 30px rgba(15,23,42,.28)';
    });
    wrapper.addEventListener('mouseleave', () => {
        wrapper.style.transform = '';
        wrapper.style.boxShadow = '0 10px 24px rgba(15,23,42,.22)';
    });

    return wrapper;
}

function resolveMapDependencies(maplibreModule, circleModule) {
    const maplibregl = maplibreModule?.Map
        ? maplibreModule
        : maplibreModule?.default;
    const circle = circleModule?.default ?? circleModule?.circle;

    const requiredMapLibreApis = [
        'Map',
        'Marker',
        'Popup',
        'NavigationControl',
        'AttributionControl',
        'LngLatBounds',
    ];

    const hasMapLibreApi = requiredMapLibreApis.every(api => typeof maplibregl?.[api] === 'function');

    if (!hasMapLibreApi) {
        throw new Error('MapLibre no expuso la API esperada para inicializar el mapa.');
    }

    if (typeof circle !== 'function') {
        throw new Error('Turf Circle no expuso una función válida para dibujar la cobertura.');
    }

    return { maplibregl, circle };
}

function showMapError(element, message) {
    element.replaceChildren();
    element.classList.add('grid', 'place-items-center', 'bg-slate-50');

    const card = document.createElement('div');
    card.className = 'mx-auto max-w-md rounded-2xl border border-red-100 bg-white p-5 text-center shadow-sm';

    const title = document.createElement('div');
    title.className = 'text-sm font-extrabold text-slate-900';
    title.textContent = 'No se pudo cargar el mapa';

    const detail = document.createElement('div');
    detail.className = 'mt-2 text-xs leading-5 text-slate-500';
    detail.textContent = message || 'Recargá la página e intentá nuevamente.';

    card.append(title, detail);
    element.append(card);
}

export async function initExpressMap() {
    const element = document.getElementById('map');
    if (!element) return;

    let maplibregl;
    let circle;

    try {
        const [maplibreModule, circleModule] = await Promise.all([
            import('maplibre-gl'),
            import('@turf/circle'),
            import('maplibre-gl/dist/maplibre-gl.css'),
        ]);

        ({ maplibregl, circle } = resolveMapDependencies(maplibreModule, circleModule));
    } catch (error) {
        showMapError(element, error instanceof Error ? error.message : 'Error al cargar las dependencias del mapa.');
        console.error('No se pudieron cargar las dependencias del mapa Express:', error);
        return;
    }

    const apiKey = element.dataset.maptilerKey || '';
    const address = parseJson(element.dataset.address, {});
    const branches = parseJson(element.dataset.branches, []);
    const maxKm = Number(element.dataset.maxKm || 10) || 10;
    const currency = element.dataset.currency || '₡';

    window.__latinaExpressMap?.remove?.();

    const style = apiKey
        ? `https://api.maptiler.com/maps/streets-v2/style.json?key=${encodeURIComponent(apiKey)}`
        : fallbackStyle();

    const map = new maplibregl.Map({
        container: element,
        style,
        center: [-84.09, 9.936],
        zoom: 12,
        attributionControl: false,
        pitchWithRotate: false,
        dragRotate: false,
    });
    window.__latinaExpressMap = map;

    map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');
    map.addControl(new maplibregl.AttributionControl({ compact: true }), 'bottom-right');

    const hasCoordinates = item => normalizePair(item?.latitud, item?.longitud).every(value => value !== null);
    const bounds = new maplibregl.LngLatBounds();
    const branchMarkers = new Map();

    const popupHtml = (title, addressText, extras = '', type = 'branch') => {
        const eyebrow = type === 'address' ? 'Tu dirección' : 'Latina Pizza';
        const extraLine = extras
            ? `<div style="margin-top:8px;color:#475569;font-size:12px;line-height:1.45">${escapeHtml(extras)}</div>`
            : '';

        return `<div style="min-width:190px;padding:2px 2px 4px;font-family:inherit">
            <div style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#dc2626;margin-bottom:5px">${eyebrow}</div>
            <div style="font-size:15px;font-weight:800;color:#0f172a;line-height:1.25">${escapeHtml(title)}</div>
            <div style="margin-top:4px;color:#64748b;font-size:12px;line-height:1.45">${escapeHtml(addressText)}</div>
            ${extraLine}
        </div>`;
    };

    const addMarker = (lat, lng, html, type = 'branch', branchId = null) => {
        const markerElement = buildMarkerElement(type);
        const popup = new maplibregl.Popup({ offset: 24, closeButton: false, maxWidth: '280px' }).setHTML(html);
        const marker = new maplibregl.Marker({ element: markerElement, anchor: 'bottom' })
            .setLngLat([lng, lat])
            .setPopup(popup)
            .addTo(map);

        if (branchId !== null) {
            branchMarkers.set(Number(branchId), { marker, element: markerElement });
        }

        bounds.extend([lng, lat]);
        return marker;
    };

    map.on('load', () => {
        if (hasCoordinates(address)) {
            const [lat, lng] = normalizePair(address.latitud, address.longitud);
            addMarker(
                lat,
                lng,
                popupHtml(address.nombre ?? 'Dirección', address.direccion_exacta ?? '', '', 'address'),
                'address',
            );
        }

        const validBranches = (Array.isArray(branches) ? branches : []).filter(hasCoordinates);
        validBranches.forEach(branch => {
            const [lat, lng] = normalizePair(branch.latitud, branch.longitud);
            const distance = branch.distancia_km !== null && branch.distancia_km !== undefined ? `${branch.distancia_km} km` : '';
            const fee = branch.delivery_fee !== null && branch.delivery_fee !== undefined ? `Delivery ${currency}${branch.delivery_fee}` : '';
            const coverage = branch.covered === false ? 'Fuera de cobertura' : 'Disponible';
            const extras = [distance, fee, coverage].filter(Boolean).join(' • ');

            addMarker(
                lat,
                lng,
                popupHtml(branch.nombre ?? `Sucursal #${branch.id}`, branch.direccion ?? '', extras),
                'branch',
                branch.id,
            );
        });

        if (!bounds.isEmpty()) {
            map.fitBounds(bounds, { padding: { top: 58, right: 58, bottom: 58, left: 58 }, maxZoom: 15, duration: 650 });
        }

        const sourceId = 'coverage-source';
        const fillLayerId = 'coverage-fill';
        const lineLayerId = 'coverage-line';

        const drawCoverage = (lat, lng) => {
            const [normalizedLat, normalizedLng] = normalizePair(lat, lng);
            if (normalizedLat === null || normalizedLng === null) return;

            const geometry = circle([normalizedLng, normalizedLat], maxKm, { steps: 72, units: 'kilometers' });
            if (map.getLayer(fillLayerId)) map.removeLayer(fillLayerId);
            if (map.getLayer(lineLayerId)) map.removeLayer(lineLayerId);
            if (map.getSource(sourceId)) map.removeSource(sourceId);

            map.addSource(sourceId, { type: 'geojson', data: geometry });
            map.addLayer({
                id: fillLayerId,
                type: 'fill',
                source: sourceId,
                paint: {
                    'fill-color': '#dc2626',
                    'fill-opacity': 0.09,
                },
            });
            map.addLayer({
                id: lineLayerId,
                type: 'line',
                source: sourceId,
                paint: {
                    'line-color': '#dc2626',
                    'line-width': 2,
                    'line-opacity': 0.7,
                    'line-dasharray': [2, 2],
                },
            });
        };

        const highlightBranch = branchId => {
            branchMarkers.forEach(({ element: markerElement }, id) => {
                const active = Number(id) === Number(branchId);
                markerElement.style.background = active ? '#991b1b' : '#dc2626';
                markerElement.style.transform = active ? 'translateY(-3px) scale(1.1)' : '';
                markerElement.style.boxShadow = active
                    ? '0 14px 32px rgba(220,38,38,.35)'
                    : '0 10px 24px rgba(15,23,42,.22)';
            });
        };

        const selectBranch = branch => {
            if (!branch) return;
            const [lat, lng] = normalizePair(branch.latitud, branch.longitud);
            drawCoverage(lat, lng);
            highlightBranch(branch.id);
            map.easeTo({ center: [lng, lat], zoom: Math.max(map.getZoom(), 13), duration: 500 });
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
