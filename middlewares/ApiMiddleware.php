<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use PDO;

/** Centraliza las comprobaciones comunes de los endpoints JSON. */
final class ApiMiddleware
{
    private static function rechazar(int $estado, string $clave, string $mensaje, int $opciones): never
    {
        http_response_code($estado);
        echo json_encode(['ok' => false, $clave => $mensaje], $opciones);
        exit;
    }

    public static function exigirMetodo(array $metodos, string $clave = 'mensaje', int $opciones = 0): void
    {
        if (! in_array($_SERVER['REQUEST_METHOD'] ?? '', $metodos, true)) {
            self::rechazar(405, $clave, 'Método no permitido.', $opciones);
        }
    }

    public static function validarCsrfJson(mixed $recibido, string $mensaje, string $clave = 'mensaje', int $opciones = 0): void
    {
        $sesion = $_SESSION['_csrf'] ?? null;
        if (! is_string($sesion) || ! is_string($recibido) || $sesion === '' || $recibido === '' || ! hash_equals($sesion, $recibido)) {
            self::rechazar(403, $clave, $mensaje, $opciones);
        }
    }

    public static function exigirSesionJson(PDO $conexion, string $mensaje): array
    {
        $usuario = AutenticacionMiddleware::validarSesionPersistida($_SESSION['user'] ?? null, new SesionDAO($conexion));
        if ($usuario === null) {
            self::rechazar(401, 'mensaje', $mensaje, 0);
        }
        return $usuario;
    }

    public static function exigirPermisoJson(PDO $conexion, array $usuario, string $permiso, string $mensaje): void
    {
        if (! (new PermisoDAO($conexion))->usuarioTiene((int) ($usuario['id'] ?? 0), $permiso)) {
            self::rechazar(403, 'mensaje', $mensaje, 0);
        }
    }
}
