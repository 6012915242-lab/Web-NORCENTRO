<?php

use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/sesion/token', [SesionController::class, 'token']);
Route::post('/sesion/iniciar', [SesionController::class, 'iniciar'])->middleware('throttle:10,1');
Route::post('/sesion/cerrar', [SesionController::class, 'cerrar'])->middleware('auth');
