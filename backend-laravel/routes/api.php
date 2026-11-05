
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\OfertaController;
use App\Http\Middleware\ExigirSesionApi;
use App\Http\Middleware\VerificarPermiso;

Route::get('/motos', [CatalogoController::class, 'index']);
Route::get('/motos/{id}', [CatalogoController::class, 'show'])->whereNumber('id');

Route::middleware(['web', ExigirSesionApi::class])->group(function (): void {
    Route::middleware(VerificarPermiso::class . ':ofertas.gestionar')->group(function (): void {
        Route::get('/ofertas', [OfertaController::class, 'index']);
        Route::post('/ofertas', [OfertaController::class, 'store']);
        Route::put('/ofertas/{id}', [OfertaController::class, 'update'])->whereNumber('id');
        Route::delete('/ofertas/{id}', [OfertaController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware(VerificarPermiso::class . ':inventario.gestionar')->group(function (): void {
        Route::get('/inventario', [InventarioController::class, 'index']);
        Route::post('/inventario', [InventarioController::class, 'store']);
    });

    Route::middleware(VerificarPermiso::class . ':clientes.gestionar')->group(function (): void {
        Route::get('/clientes', [ClienteController::class, 'index']);
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->whereNumber('id');
        Route::post('/clientes', [ClienteController::class, 'store']);
        Route::put('/clientes/{id}', [ClienteController::class, 'update'])->whereNumber('id');
    });

    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware(VerificarPermiso::class . ':clientes.desactivar');
});
