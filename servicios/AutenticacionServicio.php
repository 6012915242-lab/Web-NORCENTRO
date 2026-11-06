<?php

declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\UsuarioDAO;
use App\Modelos\Usuario;
use DomainException;

/** Aplica las reglas de autenticación sin cargar el controlador. */
final class AutenticacionServicio
{
    public function autenticar(
        UsuarioDAO $usuarioDAO,
        string $nombreUsuario,
        string $clave,
        string $direccionIp
    ): Usuario {
        $nombreUsuario = trim($nombreUsuario);

        if ($nombreUsuario === '' || $clave === '' || mb_strlen($nombreUsuario) > 50) {
            $usuarioDAO->registrarIntento($nombreUsuario, $direccionIp, false, 'Credenciales incompletas');
            throw new DomainException('Usuario o contraseña incorrectos.');
        }

        /** Busca la cuenta y verifica que pueda iniciar sesión. */
        $usuario = $usuarioDAO->buscarActivo($nombreUsuario);

        if ($usuario === null) {
            $usuarioDAO->registrarIntento($nombreUsuario, $direccionIp, false, 'Usuario inexistente o inactivo');
            throw new DomainException('Usuario o contraseña incorrectos.');
        }

        if ($usuario->estaBloqueado()) {
            $usuarioDAO->registrarIntento($nombreUsuario, $direccionIp, false, 'Cuenta bloqueada temporalmente');
            throw new DomainException('La cuenta está bloqueada temporalmente. Intenta nuevamente en 15 minutos.');
        }

        /** Comprueba la contraseña contra el hash guardado. */
        if (! password_verify($clave, $usuario->passwordHash)) {
            $usuarioDAO->registrarFallo($usuario->id);
            $usuarioDAO->registrarIntento($nombreUsuario, $direccionIp, false, 'Contraseña incorrecta');
            throw new DomainException('Usuario o contraseña incorrectos.');
        }

        $usuarioDAO->registrarAccesoCorrecto($usuario->id);
        $usuarioDAO->registrarIntento($nombreUsuario, $direccionIp, true, 'Acceso correcto');

        return $usuario;
    }
}
