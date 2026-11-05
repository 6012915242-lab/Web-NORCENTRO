
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Middleware\ExigirSesionApi;

Route::middleware(ExigirSesionApi::class)->group(function (): void {
    Route::get('/clientes', [ClienteController::class, 'index']);

    Route::get('/clientes/{id}', [ClienteController::class, 'show'])
        ->whereNumber('id');
});
