<?php

use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\Gestion\AuditoriaController;
use App\Http\Controllers\Gestion\FincaController;
use App\Http\Controllers\Gestion\InventarioController;
use App\Http\Controllers\Gestion\PedidoController as GestionPedidoController;
use App\Http\Controllers\Gestion\ProductoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidoConsumidorController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/catalogo');

// Público: catálogo y ficha de trazabilidad.
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/productos/{id}', [CatalogoController::class, 'show'])->name('productos.show');

// Carrito de compras
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::patch('/carrito/{producto}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::delete('/carrito/{producto}', [CarritoController::class, 'remover'])->name('carrito.remover');
Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');

Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [SesionController::class, 'crear'])->name('login');
    Route::post('/ingresar', [SesionController::class, 'almacenar'])
        ->middleware('throttle:20,1')->name('login.almacenar');

    Route::get('/registro', [RegistroController::class, 'crear'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'almacenar'])
        ->middleware('throttle:10,1')->name('registro.almacenar');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/salir', [SesionController::class, 'destruir'])->name('logout');

    Route::get('/panel', [PanelController::class, 'inicio'])->name('panel');

    // Checkout del consumidor
    Route::get('/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');
    Route::post('/checkout', [CarritoController::class, 'procesarCheckout'])->name('carrito.procesar');

    // Pedidos del Consumidor
    Route::get('/mis-pedidos', [PedidoConsumidorController::class, 'index'])->name('pedidos.index');
    Route::get('/mis-pedidos/{pedido}', [PedidoConsumidorController::class, 'show'])->name('pedidos.show');
    Route::post('/mis-pedidos/{pedido}/pago', [PedidoConsumidorController::class, 'registrarPago'])->name('pedidos.pago');
    Route::post('/mis-pedidos/{pedido}/calificar-item', [PedidoConsumidorController::class, 'calificarItem'])->name('pedidos.calificar-item');
    Route::post('/entregas/{entrega}/calificar', [PedidoConsumidorController::class, 'calificarEntrega'])->name('entregas.calificar');

    // Reportes analíticos (las 5 consultas NoSQL)
    Route::middleware('rol:Productor')->get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Paneles por rol
    Route::middleware('rol:Administrador')->get('/admin', [PanelController::class, 'administrador'])->name('admin.panel');
    Route::middleware('rol:Productor')->get('/productor', [PanelController::class, 'productor'])->name('productor.panel');
    Route::middleware('rol:Consumidor')->get('/consumidor', [PanelController::class, 'consumidor'])->name('consumidor.panel');

    // Gestión de la oferta: Productor (solo lo suyo) y Administrador (todo).
    Route::middleware('rol:Productor')->prefix('gestion')->name('gestion.')->group(function (): void {
        Route::resource('productos', ProductoController::class)->except('show');
        Route::resource('fincas', FincaController::class)->except('show');

        Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::patch('inventario/{producto}', [InventarioController::class, 'actualizar'])->name('inventario.actualizar');

        // Pedidos, pagos y entregas para productores y administradores
        Route::get('pedidos', [GestionPedidoController::class, 'index'])->name('pedidos.index');
        Route::get('pedidos/{pedido}', [GestionPedidoController::class, 'show'])->name('pedidos.show');
        Route::patch('pedidos/{pedido}/estado', [GestionPedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
        Route::patch('pagos/{pago}/conciliar', [GestionPedidoController::class, 'conciliarPago'])->name('pagos.conciliar');
        Route::patch('entregas/{entrega}', [GestionPedidoController::class, 'actualizarEntrega'])->name('entregas.actualizar');

        // Auditoría básica (exclusivo Administrador)
        Route::middleware('rol:Administrador')->get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });
});
