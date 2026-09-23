# Latina Pizza

Sistema de pedidos compuesto por dos aplicaciones Laravel 12:

- `latina-pizza-api`: API, reglas de negocio, PostgreSQL, Stripe, facturas y panel de cocina.
- `latina-pizza-web`: interfaz web que consume la API.

Ambas aplicaciones usan la misma base de datos. El API es el dueño de las migraciones del esquema compartido; no ejecute las migraciones del proyecto web sobre una base que ya fue preparada por el API.

## Requisitos

- PHP 8.2 o superior con `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo` y `dom`.
- Composer 2.
- PostgreSQL 14 o superior.
- Node.js 20 o 22 y npm.

## Instalación local en Windows

Clone el repositorio y prepare primero el API:

```powershell
cd latina-pizza-api
Copy-Item .env.example .env
composer install
php artisan key:generate
```

Edite `.env` y configure al menos `DB_PASSWORD`. Para crear el primer administrador al sembrar la base, defina también:

```dotenv
SEED_ADMIN_NAME="Administrador"
SEED_ADMIN_EMAIL="correo@ejemplo.com"
SEED_ADMIN_PASSWORD="use-una-clave-larga-y-unica"
```

Luego prepare el esquema y levante el API:

```powershell
php artisan migrate --seed
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

En otra terminal prepare la aplicación web:

```powershell
cd latina-pizza-web
Copy-Item .env.example .env
composer install
php artisan key:generate
npm ci
npm run build
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Abra `http://127.0.0.1:8000`. Mantenga ambas terminales abiertas.

El registro exige verificar el correo antes de comprar. Con `MAIL_MAILER=log`, el enlace de verificación queda en `latina-pizza-web/storage/logs/laravel.log`; en producción configure un proveedor SMTP real.

La recuperación de contraseña usa un código enviado por el **API**. Inicie también, desde `latina-pizza-api`, `php artisan queue:work --queue=password-recovery --timeout=30`. Configure SMTP en el API para entregar los correos. Con `MAIL_MAILER=log`, el código se escribe solo en el log local del API. Consulte [la arquitectura, configuración y verificación de recuperación](docs/PASSWORD_RECOVERY.md).

## Actualizar una base existente

Antes de actualizar, haga un respaldo de PostgreSQL. Después de traer la rama o versión nueva:

```powershell
git pull
cd latina-pizza-api
composer install
php artisan migrate
php artisan optimize:clear

cd ..\latina-pizza-web
composer install
npm ci
npm run build
php artisan optimize:clear
```

No use `migrate:fresh` en una base con pedidos reales: elimina todas las tablas y sus datos.

## Pruebas

En cada proyecto Laravel:

```powershell
composer install
php artisan test
vendor\bin\pint --test
```

En el proyecto web:

```powershell
npm ci
npm run build
```

GitHub Actions ejecuta estas verificaciones automáticamente en cada push y pull request.

## Stripe

La llave secreta existe únicamente en `latina-pizza-api/.env`:

```dotenv
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=crc
```

En `latina-pizza-web/.env` coloque solamente `STRIPE_KEY`. El webhook debe apuntar a:

```text
POST https://su-api.example.com/api/stripe/webhook
```

Pruebe primero con llaves y tarjetas de prueba de Stripe. Nunca suba `.env`, llaves, contraseñas, respaldos o archivos ZIP al repositorio.

Los administradores pueden iniciar un reembolso total de Stripe desde el detalle del pedido. Antes de usar dinero real, pruebe pago, webhook, reembolso y reintentos en modo de prueba.

## Datos del negocio

Complete en `latina-pizza-web/.env` la dirección, teléfono, correo y enlaces sociales `BUSINESS_*`. Los enlaces vacíos no se muestran. La factura PDF del sistema es un comprobante de pedido; no sustituye por sí sola la facturación electrónica fiscal que corresponda al negocio.

## Flujo recomendado de Git

No descargue y copie carpetas encima del proyecto. Trabaje con ramas:

```powershell
git fetch origin
git switch codex/production-hardening
git pull
```

Después de validar localmente, integre mediante un pull request hacia `main`. Para empezar otro cambio:

```powershell
git switch main
git pull --ff-only origin main
git switch -c feature/nombre-del-cambio
```

Consulte [docs/PRODUCTION_CHECKLIST.md](docs/PRODUCTION_CHECKLIST.md) antes de vender o desplegar el sistema.
