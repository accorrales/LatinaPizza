import test from 'node:test';
import assert from 'node:assert/strict';
import { progressFor, addressFor } from '../resources/js/pages/tracking.js';

test('terminal states override stale kitchen state', () => {
    assert.equal(progressFor({ estado: 'cancelado', cocina: 'listo' }), -1);
    assert.equal(progressFor({ estado: 'entregado', cocina: 'nuevo' }), 4);
    assert.equal(progressFor({ estado: 'pagado', cocina: 'preparacion' }), 1);
    assert.equal(progressFor({ estado: 'pendiente', cocina: 'listo' }), 2);
});

test('pickup never displays an old delivery address', () => {
    assert.equal(addressFor({ tipo: 'pickup', sucursal: 'Centro', direccion: { direccion_exacta: 'Privada' } }), 'Retiro en sucursal: Centro');
    assert.equal(addressFor({ tipo: 'express' }), 'Dirección no registrada.');
    assert.equal(addressFor({ tipo: 'express', direccion: { direccion_exacta: 'Calle 1', telefono_contacto: '123' } }), 'Calle 1\nContacto: 123');
});
