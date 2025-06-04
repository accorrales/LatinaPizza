<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\API\ProductoController;
use App\Http\Controllers\API\CategoriaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\API\Admin\PedidoAdminController;
use App\Http\Controllers\API\HistorialPedidoController;

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

Route::middleware('auth:sanctum')->apiResource('productos', ProductoController::class);

Route::middleware('auth:sanctum')->apiResource('categorias', CategoriaController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
});

Route::middleware('auth:sanctum')->get('/mis-pedidos', [PedidoController::class, 'misPedidos']);

Route::middleware('auth:sanctum')->get('/pedidos/{id}', [PedidoController::class, 'detallePedido']);

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
