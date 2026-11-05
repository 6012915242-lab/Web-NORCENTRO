<?php

namespace App\Services;

use App\AccesoDatos\UsuarioDAO;
use App\Models\Usuario;
use App\Servicios\AutenticacionServicio;
use Illuminate\Support\Facades\DB;

class SesionServicio
{
    public function autenticar(string $nombreUsuario, string $clave, string $direccionIp): Usuario
    {
        $dao = new UsuarioDAO(DB::connection()->getPdo());
        $entidad = (new AutenticacionServicio())->autenticar(
            $dao,
            $nombreUsuario,
            $clave,
            $direccionIp
        );

        return Usuario::query()->where('estado', 'Activo')->findOrFail($entidad->id);
    }
}
