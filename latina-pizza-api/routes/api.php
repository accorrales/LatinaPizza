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

Route::apiResource('productos', ProductoController::class)->only(['index', 'show']);
Route::apiResource('categorias', CategoriaController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('productos', ProductoController::class)->except(['index', 'show']);
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

Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
    Route::get('/resumen-sucursal/{id}', [PedidoAdminController::class, 'resumenSucursal']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/carrito', [CarritoController::class, 'index']);
    Route::post('/carrito/add', [CarritoController::class, 'add']);
    Route::delete('/carrito/remove/{id}', [CarritoController::class, 'remove']);
    Route::delete('/carrito/clear', [CarritoController::class, 'clear']);
    Route::post('/stripe/checkout', [StripeController::class, 'checkout']);
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

Route::apiResource('promociones', PromocionController::class)->only(['index', 'store']);
Route::get('/promociones/{id}', [PromocionController::class, 'show']);
Route::get('/detalle-pedido-promocion/{pedido_id}/detalles', [DetallePedidoPromocionController::class, 'detallesConPrecioYDesglose']);

Route::middleware(['auth:sanctum', CheckRole::class . ':admin'])->prefix('admin')->group(function () {
    Route::get('/pedidos', [PedidoAdminController::class, 'index']); // ✅ Listado + filtros
    Route::put('/pedidos/{id}/estado', [PedidoAdminController::class, 'actualizarEstado']); // ✅ Cambiar estado
    Route::get('/pedidos/{id}/historial', [PedidoAdminController::class, 'verHistorial']); // ✅ Ver historial
    Route::get('/tiempo-estimado', [PedidoAdminController::class, 'tiempoEstimado']); // ✅ Tiempo estimado
    Route::get('/resumen-sucursal/{id}', [PedidoAdminController::class, 'resumenSucursal']); // ✅ Resumen por sucursal
    Route::get('/pedidos/{id}/ver', [PedidoAdminController::class, 'verPedido']);
});