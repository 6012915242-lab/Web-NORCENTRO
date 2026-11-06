<?php

namespace App\Http\Controllers;

use App\Services\SesionServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SesionController extends Controller
{
    public function token(Request $solicitud): JsonResponse
    {
        return response()->json(['token' => $solicitud->session()->token()]);
    }

    public function iniciar(Request $solicitud, SesionServicio $servicio): JsonResponse
    {
        $datos = $solicitud->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        try {
            $usuario = $servicio->autenticar(
                $datos['usuario'],
                $datos['password'],
                $solicitud->ip() ?? ''
            );
        } catch (DomainException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        Auth::login($usuario);
        $solicitud->session()->regenerate();

        return response()->json([
            'mensaje' => 'Sesión iniciada.',
            'nombre' => $usuario->nombre
        ]);
    }

    public function cerrar(Request $solicitud): JsonResponse
    {
        Auth::logout();
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }
}
