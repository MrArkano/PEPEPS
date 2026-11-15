<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClimaFincaController;
use App\Http\Controllers\Api\V1\PagoController;
use App\Http\Controllers\Api\V1\PedidoController;
use App\Http\Controllers\Api\V1\ProductoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - PEPEPS (Mercado Campesino Digital de Santander)
|--------------------------------------------------------------------------
| Arquitectura RESTful en capas con versionamiento v1, autenticación por
| tokens criptográficos (Laravel Sanctum) e integraciones con APIs externas.
*/

Route::prefix('v1')->group(function (): void {
    // -------------------------------------------------------------------------
    // Endpoints Públicos de Autenticación
    // -------------------------------------------------------------------------
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/registro', [AuthController::class, 'registro']);

    // -------------------------------------------------------------------------
    // Catálogo Campesino (Público)
    // -------------------------------------------------------------------------
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);

    // -------------------------------------------------------------------------
    // API Externa 1: Clima Agropecuario en Fincas de Santander (Open-Meteo API)
    // -------------------------------------------------------------------------
    Route::get('/fincas/{id}/clima', [ClimaFincaController::class, 'show']);

    // -------------------------------------------------------------------------
    // API Externa 2 (Webhook): Notificaciones Asíncronas de Pagos
    // -------------------------------------------------------------------------
    Route::post('/pagos/webhook', [PagoController::class, 'webhook']);

    // -------------------------------------------------------------------------
    // Endpoints Protegidos mediante Tokens Criptográficos (Laravel Sanctum)
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/perfil', [AuthController::class, 'perfil']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Gestión de Pedidos
        Route::get('/pedidos', [PedidoController::class, 'index']);
        Route::post('/pedidos', [PedidoController::class, 'store']);
        Route::get('/pedidos/{id}', [PedidoController::class, 'show']);

        // API Externa 2 (Intención de Pago con Pasarela Stripe)
        Route::post('/pedidos/{id}/pagar', [PagoController::class, 'checkoutIntent']);
    });
});
