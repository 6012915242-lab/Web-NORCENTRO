
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteController;

Route::get('/clientes', [ClienteController::class, 'index']);

Route::get('/clientes/{id}', [ClienteController::class, 'show'])
    ->whereNumber('id');
