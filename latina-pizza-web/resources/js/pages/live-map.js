export function validLocation(location) {
    return location && typeof location.latitude === 'number' && Number.isFinite(location.latitude)
        && Math.abs(location.latitude) <= 90 && typeof location.longitude === 'number'
        && Number.isFinite(location.longitude) && Math.abs(location.longitude) <= 180;
}

export function isStale(location, now = Date.now()) {
    const recorded = Date.parse(location?.recorded_at);
    return !Number.isFinite(recorded) || now - recorded > 60000;
}

// Factory separated from the DOM controller so marker movement/cleanup can be tested.
export function createLiveMap(container, maplibre, onError) {
    const map = new maplibre.Map({
        container,
        style: { version: 8, sources: { osm: { type: 'raster', tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256, attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' } }, layers: [{ id: 'osm', type: 'raster', source: 'osm' }] },
        center: [-84.08, 9.93], zoom: 14,
    });
    map.addControl(new maplibre.NavigationControl(), 'top-right');
    map.on('error', onError);
    let marker;
    return {
        update(location) {
            if (!validLocation(location)) return;
            const coordinates = [location.longitude, location.latitude];
            if (!marker) {
                marker = new maplibre.Marker({ color: '#2563eb' }).setLngLat(coordinates).addTo(map);
                map.jumpTo({ center: coordinates, zoom: 15 });
            } else {
                marker.setLngLat(coordinates);
            }
            map.resize();
        },
        destroy() { marker?.remove(); map.remove(); },
    };
}
