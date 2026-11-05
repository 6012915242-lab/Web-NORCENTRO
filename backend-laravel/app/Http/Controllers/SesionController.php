<?php

namespace App\Http\Controllers;

use App\AccesoDatos\UsuarioDAO;
use App\Models\Usuario;
use App\Servicios\AutenticacionServicio;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SesionController extends Controller
{
    public function token(Request $solicitud): JsonResponse
    {
        return response()->json(['token' => $solicitud->session()->token()]);
    }

    public function iniciar(Request $solicitud, AutenticacionServicio $servicio): JsonResponse
    {
        $datos = $solicitud->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        try {
            $entidad = $servicio->autenticar(
                new UsuarioDAO(DB::connection()->getPdo()),
                $datos['usuario'],
                $datos['password'],
                $solicitud->ip() ?? ''
            );
        } catch (DomainException $error) {
            return response()->json(['mensaje' => $error->getMessage()], 422);
        }

        $usuario = Usuario::query()->where('estado', 'Activo')->findOrFail($entidad->id);
        Auth::login($usuario);
        $solicitud->session()->regenerate();

        return response()->json(['mensaje' => 'Sesión iniciada.', 'nombre' => $usuario->nombre]);
    }

    public function cerrar(Request $solicitud): JsonResponse
    {
        Auth::logout();
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }
}
