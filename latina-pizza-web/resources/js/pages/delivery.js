function toRad(value) {
    return value * Math.PI / 180;
}

function distanceKm(lat1, lon1, lat2, lon2) {
    const radius = 6371;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
    return radius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}

function initPickupDistanceSorting() {
    const container = document.getElementById('sucursales-lista');
    if (!container || !navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(position => {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        const cards = Array.from(container.children);

        cards.forEach(card => {
            const label = card.querySelector('.dist-valor');
            if (!label) return;

            const lat = Number.parseFloat(label.dataset.lat);
            const lng = Number.parseFloat(label.dataset.lng);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                const distance = distanceKm(userLat, userLng, lat, lng);
                label.textContent = `${distance.toFixed(2)} km`;
                card.dataset.dist = String(distance);
            } else {
                card.dataset.dist = String(Number.MAX_SAFE_INTEGER);
            }
        });

        cards
            .sort((a, b) => Number(a.dataset.dist) - Number(b.dataset.dist))
            .forEach(card => container.appendChild(card));
    }, () => {}, { enableHighAccuracy: true, timeout: 8000 });
}

function initExpressGeolocation() {
    const button = document.getElementById('btnUbicacion');
    const status = document.getElementById('geoStatus');
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');

    if (!button || !status || !latInput || !lngInput) return;

    button.addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Geolocalización no soportada en este navegador.';
            return;
        }

        status.textContent = 'Obteniendo ubicación…';
        navigator.geolocation.getCurrentPosition(position => {
            latInput.value = position.coords.latitude.toFixed(6);
            lngInput.value = position.coords.longitude.toFixed(6);
            status.textContent = 'Ubicación lista ✅';
        }, error => {
            status.textContent = `No se pudo obtener la ubicación (${error.message})`;
        }, { enableHighAccuracy: true, timeout: 10000 });
    });
}

export function initDeliveryPages() {
    initPickupDistanceSorting();
    initExpressGeolocation();
}
