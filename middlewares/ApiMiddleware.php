<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use PDO;

/**
 * Middleware de los endpoints JSON (public/api).
 *
 * Aplica a las APIs las mismas reglas que el Front Controller aplica a las páginas:
 * método HTTP permitido, token CSRF, sesión válida y permiso RBAC.
 * Si una comprobación falla, responde un JSON de error y detiene la petición.
 */
final class ApiMiddleware
{
    private const OPCIONES_JSON = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /** Prepara la petición: sesión segura, cabeceras de seguridad y respuesta JSON sin caché. */
    public static function iniciar(): void
    {
        SeguridadMiddleware::inicializarEntornoSeguro(dirname(__DIR__).'/storage/sessions');
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
    }

    /** Envía una respuesta JSON con el código HTTP indicado y termina la ejecución. */
    public static function responder(int $estadoHttp, array $datos): never
    {
        http_response_code($estadoHttp);
        echo json_encode($datos, self::OPCIONES_JSON);
        exit;
    }

    /** Respuesta de error con el formato común: {"ok": false, "mensaje": "..."}. */
    public static function error(int $estadoHttp, string $mensaje): never
    {
        self::responder($estadoHttp, ['ok' => false, 'mensaje' => $mensaje]);
    }

    /** @param  list<string>  $metodosPermitidos  por ejemplo ['GET', 'POST'] */
    public static function exigirMetodo(array $metodosPermitidos): void
    {
        if (! in_array($_SERVER['REQUEST_METHOD'] ?? '', $metodosPermitidos, true)) {
            self::error(405, 'Método no permitido.');
        }
    }

    /** Compara el token recibido con el de la sesión en tiempo constante (hash_equals). */
    public static function validarCsrf(mixed $tokenRecibido, string $mensaje): void
    {
        $tokenSesion = $_SESSION['_csrf'] ?? null;
        $esValido = is_string($tokenSesion) && $tokenSesion !== ''
            && is_string($tokenRecibido) && $tokenRecibido !== ''
            && hash_equals($tokenSesion, $tokenRecibido);

        if (! $esValido) {
            self::error(403, $mensaje);
        }
    }

    /** Devuelve el usuario de la sesión o responde 401 si no hay una sesión válida. */
    public static function exigirSesion(PDO $conexion, string $mensaje): array
    {
        $usuario = AutenticacionMiddleware::validarSesionPersistida(
            $_SESSION['user'] ?? null,
            new SesionDAO($conexion)
        );

        if ($usuario === null) {
            self::error(401, $mensaje);
        }

        return $usuario;
    }

    /** Responde 403 si el rol del usuario no tiene el permiso indicado (RBAC). */
    public static function exigirPermiso(PDO $conexion, array $usuario, string $codigoPermiso, string $mensaje): void
    {
        $usuarioId = (int) ($usuario['id'] ?? 0);

        if (! (new PermisoDAO($conexion))->usuarioTiene($usuarioId, $codigoPermiso)) {
            self::error(403, $mensaje);
        }
    }
}
