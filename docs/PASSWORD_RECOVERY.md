# Recuperación de contraseña por código

## Arquitectura revisada

El repositorio tiene dos aplicaciones Laravel 12 con una base PostgreSQL compartida. El API mantiene las migraciones, usuarios y roles, Sanctum, pedidos, catálogo, pagos, facturas y correos transaccionales. La web usa Blade, Tailwind y Vite, autentica mediante el guard de sesión local y solicita un token Sanctum al API al iniciar sesión. El registro web escribe el mismo usuario compartido y envía la verificación desde la web. Los correos de pedidos pertenecen al API.

Antes de este cambio ambas aplicaciones usaban el broker de enlaces de Laravel de forma independiente. Ahora solo el API emite y valida códigos; la web llama al API con tiempos de espera y sin reintentos automáticos. No hay una segunda base de credenciales ni claves de aplicación compartidas. Los enlaces de recuperación anteriores conducen a solicitar un código nuevo y sus tokens ya no autorizan cambios.

## Contrato y experiencia

- `POST /api/forgot-password`: `email`. Respuesta 200 genérica para cuentas existentes e inexistentes. Encola la consulta y el envío sin esperar al SMTP.
- `POST /api/reset-password`: `email`, `code` (cadena de seis dígitos, incluidos ceros iniciales), `password`, `password_confirmation`. Devuelve 200 al cambiar la contraseña; 422 para código inválido, vencido o bloqueado y errores de validación; 429 con `Retry-After` para límites.
- La web mantiene `/forgot-password` y `/reset-password`, con CSRF, mensajes en español, reenvío, contador de espera, pegado/autocompletado del código y contraseñas visibles opcionalmente. Funciona también sin JavaScript. El correo se conserva en sesión; nunca se incluyen código ni contraseña en URLs o datos de formulario guardados tras errores.
- La recuperación exige al menos 12 caracteres y hasta 72 bytes por compatibilidad con bcrypt. Los flujos existentes de registro/cambio de contraseña conservan su política anterior; unificar esa política queda fuera del cambio.

## Controles

Código generado con `random_int`, vencimiento de 10 minutos desde su emisión, cinco intentos fallidos y un solo uso. Se guarda un HMAC-SHA-256 con la clave del API, ligado al usuario, correo y hash de contraseña actual. Un cambio de credenciales invalida el código pendiente. La fila del usuario se bloquea en una transacción para serializar emisión, reenvío y consumo. Los intentos fallidos se confirman en la base, incluso cuando se rechaza la solicitud.

Hay 60 segundos entre emisiones, tres solicitudes por correo cada 15 minutos y diez solicitudes de cambio por correo cada 15 minutos. Estos límites incluyen direcciones inexistentes y normalizan mayúsculas/espacios. El límite general del API es 100 solicitudes por IP/minuto por operación, porque el servidor web actúa como proxy. La web limita a cinco solicitudes por IP/minuto por operación. Los intentos por código están en PostgreSQL; los límites HTTP requieren una caché central compartida entre instancias (database o Redis). No se aceptan IPs enviadas por el navegador como identidad de confianza.

El cambio elimina los tokens Sanctum de ese usuario, sus filas en `sessions`, el código y tokens antiguos del broker; también rota `remember_token`. `AuthenticateSession` en la web detecta hashes de contraseña modificados. El login guarda la referencia desde su primera respuesta. El despliegue existente usa `SESSION_DRIVER=database` y la misma tabla compartida `sessions`; mantenga esa configuración para revocar también sesiones anteriores a este despliegue. No se inicia sesión automáticamente ni se marca el correo como verificado. Se encola un aviso de contraseña cambiada sin incluir secretos.

## Despliegue

1. Desplegar API y web en una ventana coordinada: el contrato cambia de `token` a `code`.
2. Ejecutar **solo en el API** `php artisan migrate --force`. No ejecutar migraciones web contra la base compartida.
3. Configurar `APP_ENV=production`, `APP_DEBUG=false`, HTTPS y cookies seguras en ambas aplicaciones. Mantener `APP_KEY` estable y privada por aplicación.
4. Configurar en el API `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` y un `MAIL_FROM_ADDRESS` autorizado por el proveedor. Validar SPF/DKIM y entrega en una casilla de prueba. El modo `log` es solo para desarrollo: contiene códigos y no entrega correo.
5. Mantener `QUEUE_CONNECTION=database` (o un backend asíncrono compatible) en el API. `sync` y `null` se rechazan en producción. Ejecutar y supervisar desde **latina-pizza-api**:

   ```sh
   php artisan queue:work --queue=password-recovery --timeout=30
   ```

   La cola dedicada evita que un worker web procese clases y trabajos cifrados del API. Los jobs de emisión están cifrados, tienen un intento y descartan solicitudes con más de 10 minutos de antigüedad. Si falla SMTP, el worker registra el fallo y el usuario puede reenviar; no se modifica la contraseña. Supervisar trabajos fallidos y retrasos de cola. El aviso posterior admite tres intentos; un fallo de ese aviso no revierte una contraseña ya cambiada.
6. Ejecutar `php artisan optimize:clear` en ambas aplicaciones, `npm ci && npm run build` en web y reiniciar los workers del API tras desplegar. Mantener el scheduler del API (`php artisan schedule:run` cada minuto): elimina códigos vencidos diariamente; también se puede ejecutar `php artisan auth:prune-recovery-codes` manualmente.
7. Configurar correctamente proxies confiables en el hosting para que el límite web use la IP del cliente. No confiar indiscriminadamente en encabezados reenviados.

Una caída de API/cola produce un error recuperable en la web. La aceptación de una solicitud no garantiza entrega de correo; no revela si hay cuenta. La cola debe permanecer supervisada. No registrar cuerpos de solicitudes de autenticación en proxies/APM y proteger el acceso a logs y jobs fallidos.

## Verificación

Ejecutar `php artisan test` y `vendor/bin/pint --test` en ambos proyectos y `npm run build` en web. Las pruebas cubren respuestas genéricas, cola, formato y almacenamiento del código, expiración exacta, reenvíos, cinco fallos desde distintas IPs, cambio previo de contraseña, cruce de cuentas, reutilización, revocación de sesiones/tokens, validación, errores de API y límites. Los tests locales usan SQLite; las transacciones con bloqueo de filas deben comprobarse además contra PostgreSQL antes de producción.

En staging, usar una cuenta controlada: solicitar código, revisar asunto y recepción, reenviar tras un minuto, comprobar que el anterior falla, guardar y confirmar que la contraseña vieja y las sesiones anteriores dejan de servir. Probar correo inexistente, expiración y fallo del worker sin usar cuentas de clientes. No se requiere enviar correo real para los tests automatizados.

Referencias: [OWASP Forgot Password](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html) y [colas cifradas de Laravel 12](https://laravel.com/docs/12.x/queues#encrypted-jobs).
