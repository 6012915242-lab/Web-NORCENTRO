
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Middleware\ExigirSesionApi;
use App\Http\Middleware\VerificarPermiso;

Route::middleware(['web', ExigirSesionApi::class, VerificarPermiso::class . ':clientes.gestionar'])
    ->group(function (): void {
    Route::get('/clientes', [ClienteController::class, 'index']);

    Route::get('/clientes/{id}', [ClienteController::class, 'show'])
        ->whereNumber('id');
});
