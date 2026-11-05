<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigirSesionApi
{
    public function handle(Request $solicitud, Closure $siguiente): Response
    {
        if (! $solicitud->user()) {
            return response()->json(['mensaje' => 'Debes iniciar sesión para acceder a este recurso.'], 401);
        }

        return $siguiente($solicitud);
    }
}
