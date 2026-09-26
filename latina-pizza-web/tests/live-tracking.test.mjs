import test from 'node:test';
import assert from 'node:assert/strict';
import { createLiveMap, validLocation, isStale } from '../resources/js/pages/live-map.js';
import { gpsPayload } from '../resources/js/pages/delivery-tracking.js';
import { progressFor, stepsFor } from '../resources/js/pages/tracking.js';

test('GPS rejects invalid points, accepts zero and detects stale signals', () => {
    assert.ok(validLocation({ latitude: 0, longitude: 0 }));
    for (const location of [null, {}, { latitude: 91, longitude: 0 }, { latitude: 0, longitude: Infinity }, { latitude: '9', longitude: 0 }]) assert.ok(!validLocation(location));
    assert.ok(isStale(null));
    assert.ok(isStale({ recorded_at: new Date(0).toISOString() }, 61000));
    assert.equal(isStale({ recorded_at: new Date(0).toISOString() }, 30000), false);
});

test('map initializes once, moves the same marker in longitude/latitude order, and cleans up', () => {
    const events = [];
    class Map {
        constructor(options) { events.push(['map', options.container]); }
        addControl() {}
        on(event, callback) { events.push(['handler', event]); }
        jumpTo(options) { events.push(['center', options.center]); }
        resize() {}
        remove() { events.push(['remove-map']); }
    }
    class Marker {
        constructor() { events.push(['marker']); }
        setLngLat(pair) { events.push(['position', pair]); return this; }
        addTo() { return this; }
        remove() { events.push(['remove-marker']); }
    }
    const map = createLiveMap('container', { Map, Marker, NavigationControl: class {} }, () => {});
    map.update({ latitude: 9.93, longitude: -84.08 });
    map.update({ latitude: 9.94, longitude: -84.09 });
    map.update({ latitude: 100, longitude: -84 });
    map.destroy();
    assert.equal(events.filter(([name]) => name === 'map').length, 1);
    assert.equal(events.filter(([name]) => name === 'marker').length, 1);
    assert.deepEqual(events.filter(([name]) => name === 'position'), [['position', [-84.08, 9.93]], ['position', [-84.09, 9.94]]]);
    assert.deepEqual(events.slice(-2), [['remove-marker'], ['remove-map']]);
});

test('phone payload retains the GPS capture time rather than inventing a fresh position', () => {
    assert.deepEqual(gpsPayload({ coords: { latitude: 9.93, longitude: -84.08, accuracy: 15 }, timestamp: 1000 }), {
        latitude: 9.93, longitude: -84.08, accuracy: 15, recorded_at: '1970-01-01T00:00:01.000Z',
    });
    assert.equal(progressFor({ estado: 'en_camino', cocina: 'listo' }), 3);
    assert.equal(progressFor({ estado: 'cancelado', cocina: 'listo' }), -1);
});

test('express includes an on-the-road step and pickup does not', () => {
    assert.deepEqual(stepsFor({ tipo: 'express' }), ['Recibido', 'Preparación', 'Listo', 'En camino', 'Entregado']);
    assert.equal(stepsFor({ tipo: 'express' })[progressFor({ estado: 'en_camino' })], 'En camino');
    assert.equal(stepsFor({ tipo: 'pickup' }).includes('En camino'), false);
    assert.equal(progressFor({ tipo: 'pickup', estado: 'entregado' }), 3);
});
