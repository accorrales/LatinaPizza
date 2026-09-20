<?php

use App\Http\Controllers\AdminCategoriaController;
use App\Http\Controllers\AdminExtraController;
use App\Http\Controllers\AdminMasaController;
use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\AdminPromocionController;
use App\Http\Controllers\AdminResenaController;
use App\Http\Controllers\AdminSaborController;
use App\Http\Controllers\AdminTamanoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AnalyticsBoardController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ExpressController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KitchenBoardController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\SucursalesExpressController;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'es'], true), 400);
    session()->put('locale', $locale);

    return back();
})->name('cambiar_idioma');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::get('/carrito/consume-pending', [CarritoController::class, 'consumePending'])->name('carrito.consume_pending');
    Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');
    Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::put('/carrito/update/{id}', [CarritoController::class, 'actualizarCantidad'])->name('carrito.update');
    Route::post('/carrito/agregar-promocion', [CarritoController::class, 'agregarPromocion'])->name('carrito.agregarPromocion');
    Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');
    Route::post('/carrito/stripe/intent', [CarritoController::class, 'createStripeIntent'])->name('carrito.stripe.intent');

    Route::get('/mis-pedidos', [PedidoController::class, 'vistaHistorial'])->name('usuario.pedidos');
    Route::get('/mis-pedidos/{id}', [PedidoController::class, 'detalleHistorial'])->name('usuario.pedidos.detalle');
    Route::get('/mis-pedidos/{id}/promocion', [PedidoController::class, 'detallePromocion'])->name('usuario.pedidos.promocion');

    Route::get('/pickup', [PickupController::class, 'index'])->name('pickup.index');
    Route::post('/pickup/seleccionar', [PickupController::class, 'seleccionar'])->name('pickup.seleccionar');
    Route::get('/express', [ExpressController::class, 'index'])->name('express.index');
    Route::post('/express/direcciones', [ExpressController::class, 'store'])->name('express.store');
    Route::post('/express/seleccionar', [ExpressController::class, 'seleccionar'])->name('express.seleccionar');
    Route::get('/sucursales/express', [SucursalesExpressController::class, 'index'])->name('sucursales.express');
    Route::post('/sucursales/express/seleccionar', [SucursalesExpressController::class, 'seleccionar'])->name('sucursales.express.seleccionar');

    Route::get('/sabor/{id}/resenas', [ResenaController::class, 'verResenas'])->name('sabor.resenas');
    Route::post('/resenas', [ResenaController::class, 'store'])->name('resenas.store');
    Route::put('/resenas/{id}', [ResenaController::class, 'update'])->name('resenas.update');
    Route::delete('/resenas/{id}', [ResenaController::class, 'destroy'])->name('resenas.destroy');
});

Route::middleware(['auth', 'verified', CheckRole::class.':admin,cocina'])->group(function () {
    Route::get('/kitchen', [KitchenBoardController::class, 'index'])->name('kitchen.index');
    Route::match(['get', 'post', 'patch'], '/kitchen/api/{path?}', [KitchenBoardController::class, 'proxy'])
        ->where('path', '.*')
        ->name('kitchen.proxy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', CheckRole::class.':admin'])
    ->group(function () {
        Route::resource('categorias', AdminCategoriaController::class);
        Route::resource('productos', AdminProductoController::class);
        Route::resource('usuarios', AdminUsuarioController::class)->only(['index', 'destroy', 'edit', 'update']);
        Route::resource('sabores', AdminSaborController::class)->except(['show']);
        Route::resource('tamanos', AdminTamanoController::class)->except(['show']);
        Route::resource('masas', AdminMasaController::class)->except(['show']);
        Route::resource('extras', AdminExtraController::class)->except(['show']);
        Route::resource('promociones', AdminPromocionController::class)->except(['show']);
        Route::resource('resenas', AdminResenaController::class)->only(['index', 'destroy']);

        Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{id}', [AdminPedidoController::class, 'show'])->name('pedidos.show');
        Route::put('/pedidos/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
        Route::post('/pedidos/{id}/refund', [AdminPedidoController::class, 'refund'])->name('pedidos.refund');
        Route::get('/pedidos/{id}/historial', [AdminPedidoController::class, 'verHistorial'])->name('pedidos.historial');
        Route::get('/ventas', [AnalyticsBoardController::class, 'index'])->name('ventas');
        Route::get('/ventas/api/{report?}', [AnalyticsBoardController::class, 'proxy'])
            ->where('report', '.*')
            ->name('ventas.proxy');
    });

require __DIR__.'/auth.php';
