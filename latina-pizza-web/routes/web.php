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
use App\Http\Controllers\AdminTamanoController;
use App\Http\Controllers\AdminMasaController;
use App\Http\Controllers\AdminExtraController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\AdminResenaController;
use App\Http\Controllers\AdminPromocionController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ExpressController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\App;

    Route::get('/', [HomeController::class, 'index'])->name('home');

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
    Route::prefix('admin/tamanos')->name('admin.tamanos.')->group(function () {
        Route::get('/', [AdminTamanoController::class, 'index'])->name('index');
        Route::get('/create', [AdminTamanoController::class, 'create'])->name('create');
        Route::post('/', [AdminTamanoController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminTamanoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminTamanoController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminTamanoController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('admin/masas')->name('admin.masas.')->group(function () {
        Route::get('/', [AdminMasaController::class, 'index'])->name('index');
        Route::get('/create', [AdminMasaController::class, 'create'])->name('create');
        Route::post('/', [AdminMasaController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminMasaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminMasaController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminMasaController::class, 'destroy'])->name('destroy');
    });


    Route::prefix('admin/extras')->name('admin.extras.')->group(function () {
        Route::get('/', [AdminExtraController::class, 'index'])->name('index');
        Route::get('/create', [AdminExtraController::class, 'create'])->name('create');
        Route::post('/', [AdminExtraController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminExtraController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminExtraController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminExtraController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/productos')->name('admin.productos.')->group(function () {
        Route::get('/', [AdminProductoController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductoController::class, 'create'])->name('create');
        Route::post('/', [AdminProductoController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminProductoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminProductoController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminProductoController::class, 'destroy'])->name('destroy');
    });
    // Reseñas: eliminación para usuarios autenticados
    Route::delete('/resenas/{id}', [ResenaController::class, 'destroy'])->name('resenas.destroy')->middleware('auth');
    Route::get('/sabor/{id}/resenas', [ResenaController::class, 'verResenas'])->name('sabor.resenas');
    Route::get('/resenas/crear/{saborId}', [ResenaController::class, 'crear'])->name('resenas.crear')->middleware('auth');
    Route::post('/resenas', [ResenaController::class, 'store'])->name('resenas.store')->middleware('auth');

    Route::prefix('admin/resenas')->name('admin.resenas.')->middleware('auth')->group(function () {
        Route::get('/', [AdminResenaController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [AdminResenaController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminResenaController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminResenaController::class, 'destroy'])->name('destroy');
    });
    Route::put('/resenas/{id}', [ResenaController::class, 'update'])->name('resenas.update')->middleware('auth');

    Route::prefix('admin/promociones')->name('admin.promociones.')->middleware('auth')->group(function () {
        Route::get('/', [AdminPromocionController::class, 'index'])->name('index');
        Route::get('/create', [AdminPromocionController::class, 'create'])->name('create');
        Route::post('/', [AdminPromocionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminPromocionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminPromocionController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminPromocionController::class, 'destroy'])->name('destroy');
    });
    Route::get('/pickup', function () {
        return view('catalogo.pickup');
    })->middleware(['auth'])->name('vista.pickup');

    Route::middleware(['auth'])->group(function () {
        Route::get('/pickup', [PickupController::class, 'index'])->name('pickup.index');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/express', [ExpressController::class, 'index'])->name('express.index');
    });

    Route::view('/pickup', 'pedido.pickup')->middleware('auth')->name('pedido.pickup');

    Route::get('/lang/{locale}', function ($locale) {
        if (!in_array($locale, ['en','es'])) abort(400);
        session()->put('locale', $locale);
        return back();
    })->name('cambiar_idioma');

require __DIR__.'/auth.php';

