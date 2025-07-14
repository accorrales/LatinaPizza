<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\AdminCategoriaController;
use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\PedidoPromocionController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\PromocionesController;
use App\Http\Controllers\AdminSaborController;
Route::get('/', function () {
    return view('home');
})->name('home');

// Authentication routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
//catalogo routes
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');

// Carrito routes
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
Route::put('/carrito/update/{id}', [CarritoController::class, 'actualizarCantidad'])->name('carrito.update');
Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');
Route::post('/carrito/agregar-promocion', [CarritoController::class, 'agregarPromocion'])->name('carrito.agregarPromocion');

// Admin routes
// This route is for the admin to manage categories
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // 🛠 Categorías
    Route::resource('categorias', AdminCategoriaController::class);

    // 🍕 Productos
    Route::resource('productos', AdminProductoController::class);

    // 👤 Usuarios
    Route::resource('usuarios', AdminUsuarioController::class)->only(['index', 'destroy', 'edit', 'update']);

    // 📦 Pedidos
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [AdminPedidoController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminPedidoController::class, 'show'])->name('show');
        Route::put('/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('estado');
        Route::get('/{id}/historial', [AdminPedidoController::class, 'verHistorial'])->name('historial');
    });
});


Route::get('/pedido-promocion/{id}/resumen', [PedidoPromocionController::class, 'mostrarResumen'])->name('pedido.promocion.resumen');

Route::get('/pedido/{id}/resumen', [PedidoController::class, 'mostrarResumen'])->name('pedido.resumen');
// Historial y detalles
Route::get('/mis-pedidos', [PedidoController::class, 'vistaHistorial'])
    ->name('usuario.pedidos')       // 👈 este es el que espera tu vista
    ->middleware('auth');           // 👈 seguís protegiendo con auth
Route::get('/pedido/{id}/detalle', [PedidoController::class, 'detalleHistorial'])->name('usuario.pedidos.detalle');
Route::get('/pedido/{id}/promocion', [PedidoController::class, 'detallePromocion'])->name('usuario.pedidos.promocion');

Route::get('/mis-pedidos/{id}', [PedidoController::class, 'detalleHistorial'])->name('usuario.pedidos.detalle');

Route::get('/mis-pedidos/{id}/promocion', [PedidoController::class, 'detallePromocion'])
    ->name('pedidos.detalle.promocion');

Route::get('/promociones', [PromocionesController::class, 'index'])->name('promociones.index');
Route::prefix('admin/sabores')->name('admin.sabores.')->group(function () {
    Route::get('/', [AdminSaborController::class, 'index'])->name('index');
    Route::get('/create', [AdminSaborController::class, 'create'])->name('create');
    Route::post('/', [AdminSaborController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AdminSaborController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminSaborController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminSaborController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/auth.php';

