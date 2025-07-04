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

// Admin routes
// This route is for the admin to manage categories
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::resource('categorias', AdminCategoriaController::class);
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::resource('productos', AdminProductoController::class);
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::resource('usuarios', AdminUsuarioController::class)->only(['index', 'destroy', 'edit', 'update']);
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

require __DIR__.'/auth.php';

