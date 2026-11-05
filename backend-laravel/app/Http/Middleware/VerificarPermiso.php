<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    public function handle(Request $solicitud, Closure $siguiente, string $permiso): Response
    {
        $usuario = $solicitud->user();

        if (! $usuario instanceof Usuario || ! $usuario->tienePermiso($permiso)) {
            return response()->json(['mensaje' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        return $siguiente($solicitud);
    }
}
