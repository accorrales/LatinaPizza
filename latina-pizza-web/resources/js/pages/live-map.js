export function validLocation(location) {
    return location && typeof Number(location.latitude) === 'number' && Number.isFinite(Number(location.latitude))
        && Math.abs(Number(location.latitude)) <= 90 && Number.isFinite(Number(location.longitude))
        && Math.abs(Number(location.longitude)) <= 180;
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
    let destinationMarker;
    let fittedDestination = false;
    return {
        update(location, destination = null) {
            if (!validLocation(location)) return;
            const coordinates = [Number(location.longitude), Number(location.latitude)];
            if (!marker) marker = new maplibre.Marker({ color: '#2563eb' }).setLngLat(coordinates).addTo(map);
            else marker.setLngLat(coordinates);

            if (validLocation(destination)) {
                const destinationCoordinates = [Number(destination.longitude), Number(destination.latitude)];
                if (!destinationMarker) destinationMarker = new maplibre.Marker({ color: '#dc2626' }).setLngLat(destinationCoordinates).addTo(map);
                else destinationMarker.setLngLat(destinationCoordinates);
                if (!fittedDestination) {
                    const bounds = new maplibre.LngLatBounds().extend(coordinates).extend(destinationCoordinates);
                    map.fitBounds(bounds, { padding: 65, maxZoom: 15, duration: 350 });
                    fittedDestination = true;
                }
            } else if (!fittedDestination) {
                map.jumpTo({ center: coordinates, zoom: 15 });
            }
            map.resize();
        },
        destroy() { marker?.remove(); destinationMarker?.remove(); map.remove(); },
    };
}
