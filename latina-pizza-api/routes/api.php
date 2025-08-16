<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\API\ProductoController;
use App\Http\Controllers\API\CategoriaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\API\Admin\PedidoAdminController;
use App\Http\Controllers\API\HistorialPedidoController;
use App\Http\Controllers\Api\SucursalController;
use App\Http\Controllers\API\CarritoController;
use App\Http\Controllers\API\StripeController;
use App\Http\Controllers\API\PagoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\API\DetallePedidoController;
use App\Http\Controllers\API\DetallePedidoPromocionController;
use App\Http\Controllers\API\PromocionController;
use App\Http\Controllers\Api\OpcionesPizzaController;
use App\Http\Controllers\Api\SaborController;
use App\Http\Controllers\Api\TamanoController;
use App\Http\Controllers\Api\MasaController; 
use App\Http\Controllers\Api\ExtraController;
use App\Http\Controllers\API\ResenaController;
use App\Http\Controllers\API\DireccionUsuarioController;
use App\Http\Controllers\API\PedidoTipoController;
use App\Http\Controllers\API\DetallePedidoExtraController;  
use App\Http\Controllers\API\EntregaController;
use App\Http\Controllers\API\ExpressController;

    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':admin'  // ✅ así, directo
    ])->get('/admin/solo-admin', function () {
        return response()->json(['message' => '✅ Acceso permitido como ADMIN']);
    });

    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':cliente'
    ])->get('/cliente/solo-cliente', function () {
        return response()->json(['message' => '🙋 Bienvenido, Cliente.']);
    });

    Route::apiResource('categorias', CategoriaController::class)->only(['index', 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::post('/pedidos', [PedidoController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->get('/mis-pedidos', [PedidoController::class, 'misPedidos']);

    Route::middleware('auth:sanctum')->get('/pedidos/{id}', [PedidoController::class, 'detallePedido']);

    Route::middleware(['auth:sanctum'])->apiResource('pedidos', PedidoController::class);

    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':admin' // ✅ forma explícita
    ])->get('/admin/pedidos', [PedidoAdminController::class, 'index']);

    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':admin'
    ])->put('/admin/pedidos/{id}/estado', [PedidoAdminController::class, 'actualizarEstado']);
    require __DIR__.'/auth.php';

    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':admin'
    ])->get('/admin/pedidos/filtrar', [PedidoAdminController::class, 'filtrar']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->get('/admin/tiempo-estimado', [PedidoAdminController::class, 'tiempoEstimado']);
    Route::middleware('auth:sanctum')->get('/pedidos/{id}/historial', [HistorialPedidoController::class, 'index']);
    Route::middleware([
        'auth:sanctum',
        CheckRole::class . ':admin'
    ])->get('/admin/pedidos/{id}/historial', [PedidoAdminController::class, 'verHistorial']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::apiResource('sucursales', SucursalController::class);
    });
    //Usuarios sin logear
    Route::get('/sucursales', [SucursalController::class, 'index']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::get('/resumen-sucursal/{id}', [PedidoAdminController::class, 'resumenSucursal']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/carrito', [CarritoController::class, 'index']);
        Route::post('/carrito/add', [CarritoController::class, 'add']);
        Route::delete('/carrito/remove/{id}', [CarritoController::class, 'remove']);
        Route::delete('/carrito/clear', [CarritoController::class, 'clear']);
        Route::post('/stripe/checkout', [StripeController::class, 'checkout']);
        Route::post('/carrito/agregar-promocion', [CarritoController::class, 'agregarPromocion']);
    });

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::apiResource('usuarios', UserController::class)->only(['index', 'show', 'update', 'destroy']);
    });

    Route::middleware('auth:sanctum')->post('/pagar-con-stripe', [StripeController::class, 'checkout']);

    Route::post('/stripe/webhook', [PagoController::class, 'webhook']);

    Route::post('/detalle-pedidos', [DetallePedidoController::class, 'store']);

    Route::post('/detalle-promocion', [DetallePedidoPromocionController::class, 'store']);

    Route::get('/detalle-promocion/{pedido_id}', [DetallePedidoPromocionController::class, 'detallesConPrecioYDesglose']);

    Route::middleware('auth:sanctum')->get('/pedidos/{id}', [PedidoController::class, 'show']);

    Route::get('/promociones/{id}', [PromocionController::class, 'show']);
    Route::get('/detalle-pedido-promocion/{pedido_id}/detalles', [DetallePedidoPromocionController::class, 'detallesConPrecioYDesglose']);
    Route::get('/sabores-con-tamanos', [ProductoController::class, 'saboresConTamanos']);

    Route::get('/masas', [OpcionesPizzaController::class, 'masas']);
    Route::get('/extras', [OpcionesPizzaController::class, 'extras']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::get('/pedidos', [PedidoAdminController::class, 'index']); // ✅ Listado + filtros
        Route::put('/pedidos/{id}/estado', [PedidoAdminController::class, 'actualizarEstado']); // ✅ Cambiar estado
        Route::get('/pedidos/{id}/historial', [PedidoAdminController::class, 'verHistorial']); // ✅ Ver historial
        Route::get('/tiempo-estimado', [PedidoAdminController::class, 'tiempoEstimado']); // ✅ Tiempo estimado
        Route::get('/resumen-sucursal/{id}', [PedidoAdminController::class, 'resumenSucursal']); // ✅ Resumen por sucursal
        Route::get('/pedidos/{id}/ver', [PedidoAdminController::class, 'verPedido']);
    });

    Route::get('/promociones', [PromocionController::class, 'index']);
    Route::get('/promociones/{id}', [PromocionController::class, 'show']);
    Route::get('/productos/bebidas', [ProductoController::class, 'bebidas']);

    Route::get('/sabores', [SaborController::class, 'index']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::get('/sabores', [SaborController::class, 'index']);
        Route::get('/tamanos', [TamanoController::class, 'index']);
    });

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::apiResource('masas', MasaController::class);
    });

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::apiResource('extras-productos', ExtraController::class);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        // Crear reseña (recibe sabor_id en el body)
        Route::post('/resenas', [ResenaController::class, 'store']);
        // Actualizar reseña
        Route::put('/resenas/{id}', [ResenaController::class, 'update']);
        // Eliminar reseña
        Route::delete('/resenas/{id}', [ResenaController::class, 'destroy']);
    });
    // Promedio de estrellas por sabor
    Route::get('/resenas-promedio/{saborId}', [ResenaController::class, 'promedio']);
    // Ver reseñas de un sabor
    Route::get('/resenas/{saborId}', [ResenaController::class, 'index']);

    Route::get('/sabores-con-resenas', [SaborController::class, 'indexConResenas']);
    Route::middleware('auth:sanctum')->get('/resenas/verificar-compra/{saborId}', [ResenaController::class, 'verificarCompra']);

    Route::get('/productos-sabores-tamanos', [ProductoController::class, 'saboresConTamanos']);
    Route::get('/productos-bebidas', [ProductoController::class, 'bebidas']);
    Route::get('/promociones', [PromocionController::class, 'index']);
    Route::get('/promociones/{id}', [PromocionController::class, 'show']);
    Route::get('/resenas-promedio/{saborId}', [ResenaController::class, 'promedio']);

    Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
        Route::get('/productos', [ProductoController::class, 'index']);
        Route::get('/productos/{id}', [ProductoController::class, 'show']);
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::put('/productos/{id}', [ProductoController::class, 'update']);
        Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

        // Promociones protegidas 
    });
    Route::post('/promociones', [PromocionController::class, 'store']); 
        Route::put('/promociones/{id}', [PromocionController::class, 'update']);
        Route::delete('/promociones/{id}', [PromocionController::class, 'destroy']);


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/direcciones', [DireccionUsuarioController::class, 'index']);
        Route::post('/direcciones', [DireccionUsuarioController::class, 'store']);
        Route::get('/direcciones/{id}', [DireccionUsuarioController::class, 'show']);
        Route::put('/direcciones/{id}', [DireccionUsuarioController::class, 'update']);
        Route::delete('/direcciones/{id}', [DireccionUsuarioController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/guardar-tipo-pedido', [PedidoTipoController::class, 'guardar'])->name('guardar.tipo.pedido');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/carrito/metodo-entrega', [EntregaController::class, 'setMetodoEntrega']);
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/express', [ExpressController::class, 'index'])->name('express.index');
        Route::post('/express/direcciones', [ExpressController::class, 'store'])->name('express.store');
        Route::post('/express/seleccionar', [ExpressController::class, 'seleccionar'])->name('express.seleccionar');
    });

    Route::middleware('auth:sanctum')->get(
        '/sucursales/cercanas',
        [SucursalController::class, 'cercanas']
    );