# Lista de salida a producción

## Obligatorio antes del primer cliente

- [ ] Dominio, HTTPS y certificados activos para web y API.
- [ ] `APP_ENV=production`, `APP_DEBUG=false` y `LOG_LEVEL=warning`.
- [ ] `SESSION_SECURE_COOKIE=true` en ambos proyectos cuando HTTPS esté activo.
- [ ] Llaves `APP_KEY` distintas, generadas en cada aplicación y guardadas como secretos.
- [ ] Contraseñas fuertes para PostgreSQL y usuarios administrativos.
- [ ] Llaves reales de Stripe almacenadas en el gestor de secretos del hosting, nunca en Git.
- [ ] Webhook de Stripe registrado y probado con eventos de pago, fallo, cancelación y reembolso.
- [ ] SMTP transaccional configurado y factura probada en Gmail, Outlook y móvil.
- [ ] Verificación de correo probada con enlaces del dominio definitivo.
- [ ] Facturación electrónica fiscal integrada o resuelta con el proveedor/contador; el PDF interno no la reemplaza.
- [ ] Copias de seguridad automáticas de PostgreSQL con una restauración de prueba documentada.
- [ ] Retención de logs, alertas de errores y monitoreo de disponibilidad.
- [ ] Términos de servicio, política de privacidad, política de cancelación/reembolso y consentimiento de cookies revisados para el país de operación.
- [ ] Prueba real de punta a punta: registro, login, pickup, express, efectivo, datáfono, Stripe, cocina, entrega, factura y reseña.
- [ ] Pago y reembolso total de Stripe probados en modo de prueba, incluidos reintentos y webhook.

## Despliegue

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Para el proyecto web:

```bash
npm ci
npm run build
```

El servidor debe ejecutar de forma permanente:

- Un proceso web para cada aplicación.
- `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` cuando se activen correos/tareas en cola.
- El scheduler de Laravel cada minuto si se agregan tareas programadas.

## Seguridad operativa

- [ ] Limitar acceso de base de datos a la red privada.
- [ ] Activar cookies `secure`, `http_only` y `same_site=lax` o más estricto.
- [ ] Configurar `CORS_ALLOWED_ORIGINS` solo con el dominio web real.
- [ ] Rotar cualquier secreto que haya sido compartido en texto, capturas o commits antiguos.
- [ ] Revisar usuarios `admin` y `cocina` mensualmente y retirar cuentas inactivas.
- [ ] Aplicar actualizaciones de Composer/npm primero en staging y después en producción.
- [ ] Mantener la rama `main` protegida y exigir CI verde antes de fusionar.

## Criterio de aceptación por versión

- [ ] `php artisan test` pasa en API y web.
- [ ] `vendor/bin/pint --test` pasa en API y web.
- [ ] `npm run build` pasa en web.
- [ ] Las migraciones se probaron sobre una copia reciente de producción.
- [ ] No hay secretos ni archivos grandes en el commit.
- [ ] Se documentaron cambios visibles, migraciones y plan de reversión.
