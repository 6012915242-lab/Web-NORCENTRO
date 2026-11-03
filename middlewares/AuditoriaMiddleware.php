<?php
declare(strict_types=1);

namespace App\Middlewares;

use PDO;

/** Registra accesos y acciones sensibles sin interrumpir la operación. */
final class AuditoriaMiddleware
{
    public static function registrar(PDO $conexion, ?array $usuario, string $accion, string $recurso): void
    {
        try {
            $consulta = $conexion->prepare(
                'INSERT INTO auditoria_seguridad
                    (usuario_id, accion, recurso, metodo_http, direccion_ip, datos)
                 VALUES (:usuario, :accion, :recurso, :metodo, :ip, :datos)'
            );
            $consulta->execute([
                'usuario' => $usuario['id'] ?? null,
                'accion' => mb_substr($accion !== '' ? $accion : 'visita', 0, 80),
                'recurso' => mb_substr($recurso, 0, 120),
                'metodo' => mb_substr((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'), 0, 10),
                'ip' => mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'datos' => json_encode(['pagina' => $_GET['page'] ?? 'inicio'], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable) {
            // La auditoría no bloquea el funcionamiento si aún no se instaló su migración.
        }
    }
}
