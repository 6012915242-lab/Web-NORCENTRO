<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\AuditoriaDAO;
use Throwable;

/** Registra acciones sensibles sin mezclar SQL con el middleware. */
final class AuditoriaMiddleware
{
    public function __construct(private AuditoriaDAO $auditoriaDAO) {}

    public function registrar(?array $usuario, string $accion, string $recurso): void
    {
        try {
            $this->auditoriaDAO->registrar([
                'usuario_id' => $usuario['id'] ?? null,
                'accion' => mb_substr($accion !== '' ? $accion : 'visita', 0, 80),
                'recurso' => mb_substr($recurso, 0, 120),
                'metodo_http' => mb_substr(
                    (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
                    0,
                    10
                ),
                'direccion_ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'resultado' => 'Exitoso',
                'datos' => json_encode(
                    ['pagina' => $_GET['page'] ?? 'inicio'],
                    JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
            ]);
        } catch (Throwable) {
            // La auditoría no debe interrumpir una operación válida del usuario.
        }
    }
}
