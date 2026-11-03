<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\UsuarioDAO;
use App\Configuracion\Herramientas;
use App\Servicios\AutenticacionServicio;
use DomainException;

/** Coordina la sesión HTTP y delega las credenciales al servicio. */
final class SesionControlador
{
    private AutenticacionServicio $servicioAutenticacion;

    public function __construct(
        private UsuarioDAO $usuarioDAO,
        ?AutenticacionServicio $servicioAutenticacion = null
    ) {
        $this->servicioAutenticacion = $servicioAutenticacion ?? new AutenticacionServicio();
    }

    public function iniciarSesion(array $datos): never
    {
        try {
            $usuario = $this->servicioAutenticacion->autenticar(
                $this->usuarioDAO,
                (string) ($datos['usuario'] ?? ''),
                (string) ($datos['password'] ?? ''),
                (string) ($_SERVER['REMOTE_ADDR'] ?? '')
            );

            session_regenerate_id(true);
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            $_SESSION['_ultima_actividad'] = time();
            $_SESSION['user'] = [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'rol' => $usuario->rol,
            ];

            Herramientas::redirigir('dashboard');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
            Herramientas::redirigir('login');
        }
    }

    public function cerrarSesion(): never
    {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));

        Herramientas::mostrarAviso('success', 'Sesión cerrada correctamente.');
        Herramientas::redirigir('login');
    }
}
