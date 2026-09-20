<?php

use App\Http\Controllers\API\Admin\PedidoAdminController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\CarritoController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\CategoriaController;
use App\Http\Controllers\API\DireccionUsuarioController;
use App\Http\Controllers\API\EntregaController;
use App\Http\Controllers\API\ExtraController;
use App\Http\Controllers\API\HistorialPedidoController;
use App\Http\Controllers\API\KitchenOrderController;
use App\Http\Controllers\API\MasaController;
use App\Http\Controllers\API\OpcionesPizzaController;
use App\Http\Controllers\API\PagoController;
use App\Http\Controllers\API\PedidoController;
use App\Http\Controllers\API\ProductoController;
use App\Http\Controllers\API\PromocionController;
use App\Http\Controllers\API\ResenaController;
use App\Http\Controllers\API\SaborController;
use App\Http\Controllers\API\StripeWebhookController;
use App\Http\Controllers\API\SucursalController;
use App\Http\Controllers\API\TamanoController;
use App\Http\Controllers\API\UserController;
use App\Http\Middleware\CheckRole;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// Public catalog.
Route::apiResource('categorias', CategoriaController::class)->only(['index', 'show']);
Route::get('/sucursales', [SucursalController::class, 'index']);
Route::get('/productos', [ProductoController::class, 'publicIndex']);
Route::get('/productos/bebidas', [ProductoController::class, 'bebidas']);
Route::get('/bebidas', [ProductoController::class, 'bebidas']);
Route::get('/productos-sabores-tamanos', [ProductoController::class, 'saboresConTamanos']);
Route::get('/sabores-con-tamanos', [ProductoController::class, 'saboresConTamanos']);
Route::get('/sabores', [SaborController::class, 'index']);
Route::get('/sabores/con-resenas', [SaborController::class, 'indexConResenas']);
Route::get('/sabores-con-resenas', [SaborController::class, 'indexConResenas']);
Route::get('/sabores/{id}', [SaborController::class, 'show']);
Route::get('/masas', [OpcionesPizzaController::class, 'masas']);
Route::get('/extras', [OpcionesPizzaController::class, 'extras']);
Route::get('/promociones', [PromocionController::class, 'index']);
Route::get('/promociones/{id}', [PromocionController::class, 'show']);
Route::get('/resenas-promedio/{saborId}', [ResenaController::class, 'promedio']);
Route::get('/resenas/{saborId}', [ResenaController::class, 'index']);

// Stripe authenticates this request with its signature, not a user session.
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/mis-pedidos', [PedidoController::class, 'misPedidos']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
    Route::get('/pedidos/{id}/historial', [HistorialPedidoController::class, 'index']);
    Route::get('/detalle-pedido-promocion/{pedido_id}/detalles', [\App\Http\Controllers\API\DetallePedidoPromocionController::class, 'detallesConPrecioYDesglose']);

    Route::get('/carrito', [CarritoController::class, 'index']);
    Route::post('/carrito/add', [CarritoController::class, 'add']);
    Route::post('/carrito/agregar-promocion', [CarritoController::class, 'agregarPromocion']);
    Route::put('/carrito/items/{id}', [CarritoController::class, 'updateQuantity']);
    Route::delete('/carrito/remove/{id}', [CarritoController::class, 'remove']);
    Route::delete('/carrito/clear', [CarritoController::class, 'clear']);
    Route::post('/carrito/metodo-entrega', [EntregaController::class, 'setMetodoEntrega']);
    Route::post('/checkout', CheckoutController::class)->middleware('throttle:10,1');
    Route::post('/pagos/stripe/intent', [PagoController::class, 'createIntent'])->middleware('throttle:20,1');

    Route::apiResource('direcciones', DireccionUsuarioController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/sucursales/cercanas', [SucursalController::class, 'cercanas']);

    Route::post('/resenas', [ResenaController::class, 'store'])->middleware('throttle:10,1');
    Route::put('/resenas/{id}', [ResenaController::class, 'update'])->middleware('throttle:10,1');
    Route::delete('/resenas/{id}', [ResenaController::class, 'destroy'])->middleware('throttle:10,1');
    Route::get('/resenas/verificar-compra/{saborId}', [ResenaController::class, 'verificarCompra']);
});

Route::middleware(['auth:sanctum', 'verified', CheckRole::class.':admin'])->group(function () {
    Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
    Route::post('/promociones', [PromocionController::class, 'store']);
    Route::put('/promociones/{id}', [PromocionController::class, 'update']);
    Route::delete('/promociones/{id}', [PromocionController::class, 'destroy']);

    Route::prefix('admin')->group(function () {
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('sabores', SaborController::class);
        Route::apiResource('tamanos', TamanoController::class);
        Route::apiResource('masas', MasaController::class);
        Route::apiResource('extras-productos', ExtraController::class);
        Route::apiResource('sucursales', SucursalController::class);
        Route::apiResource('usuarios', UserController::class)->only(['index', 'show', 'update', 'destroy']);

        Route::get('/pedidos', [PedidoAdminController::class, 'index']);
        Route::get('/pedidos/filtrar', [PedidoAdminController::class, 'filtrar']);
        Route::get('/pedidos/{id}/ver', [PedidoAdminController::class, 'verPedido']);
        Route::put('/pedidos/{id}/estado', [PedidoAdminController::class, 'actualizarEstado']);
        Route::post('/pedidos/{pedido}/refund', [PedidoAdminController::class, 'refund'])
            ->middleware('throttle:5,1');
        Route::get('/pedidos/{id}/historial', [PedidoAdminController::class, 'verHistorial']);
        Route::get('/tiempo-estimado', [PedidoAdminController::class, 'tiempoEstimado']);
        Route::get('/resumen-sucursal/{id}', [PedidoAdminController::class, 'resumenSucursal']);

        Route::get('/facturas/{pedido}', function (\App\Models\Pedido $pedido) {
            return Pdf::loadView('pdf.factura', ['pedido' => $pedido])
                ->setPaper('a4')
                ->stream("Factura-{$pedido->id}.pdf");
        });
    });

    Route::prefix('analytics')->group(function () {
        Route::get('/sales/daily', [AnalyticsController::class, 'daily']);
        Route::get('/sales/weekly', [AnalyticsController::class, 'weekly']);
        Route::get('/sales/monthly', [AnalyticsController::class, 'monthly']);
        Route::get('/products/top', [AnalyticsController::class, 'topProducts']);
    });
});

Route::middleware(['auth:sanctum', 'verified', CheckRole::class.':admin,cocina'])
    ->prefix('kitchen')
    ->group(function () {
        Route::get('/orders', [KitchenOrderController::class, 'index']);
        Route::get('/orders/{pedido}', [KitchenOrderController::class, 'show']);
        Route::patch('/orders/{pedido}/status', [KitchenOrderController::class, 'updateStatus']);
        Route::patch('/orders/{pedido}/priority', [KitchenOrderController::class, 'updatePriority']);
        Route::patch('/orders/{pedido}/notes', [KitchenOrderController::class, 'updateNotes']);
        Route::patch('/orders/{pedido}/sla', [KitchenOrderController::class, 'updateSla']);
        Route::patch('/orders/{pedido}/promised', [KitchenOrderController::class, 'updatePromised']);
        Route::patch('/orders/{pedido}/ready', [KitchenOrderController::class, 'markReady']);
        Route::post('/orders/{pedido}/take', [KitchenOrderController::class, 'take']);
        Route::post('/orders/{pedido}/release', [KitchenOrderController::class, 'release']);
        Route::post('/orders/bulk/status', [KitchenOrderController::class, 'bulkStatus']);
    });
