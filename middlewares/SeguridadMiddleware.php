<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Configuracion\Herramientas;

/** Inicializa la sesión y aplica saneamiento y CSRF de forma centralizada. */
final class SeguridadMiddleware
{
    public static function inicializarEntornoSeguro(string $rutaSesion): void
    {
        if (! is_dir($rutaSesion)) {
            @mkdir($rutaSesion, 0770, true);
        }
        if (is_dir($rutaSesion) && is_writable($rutaSesion)) {
            session_save_path($rutaSesion);
        }
        session_set_cookie_params([
            'httponly' => true,
            'secure' => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        self::enviarCabecerasSeguridad();
        self::validarTiempoSesion();
    }

    /** Cabeceras HTTP que reducen ataques de clickjacking, MIME sniffing y XSS. */
    public static function enviarCabecerasSeguridad(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: frame-ancestors 'none'; object-src 'none'; base-uri 'self'; form-action 'self'");
        header_remove('X-Powered-By');
    }

    public static function sanearArregloEntrada(array $entrada): array
    {
        $resultado = [];
        foreach ($entrada as $clave => $valor) {
            $claveSegura = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $clave);
            if ($claveSegura === '') {
                continue;
            }
            $resultado[$claveSegura] = is_array($valor)
                ? self::sanearArregloEntrada($valor)
                : trim((string) $valor);
        }

        return $resultado;
    }

    public static function validarPeticionPostCsrf(): void
    {
        Herramientas::verificarCsrf();
    }

    private static function validarTiempoSesion(): void
    {
        if (empty($_SESSION['user'])) {
            return;
        }

        $ultimaActividad = (int) ($_SESSION['_ultima_actividad'] ?? time());
        $tiempoMaximoInactivo = 30 * 60;

        if (time() - $ultimaActividad > $tiempoMaximoInactivo) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['_flash'] = ['error', 'La sesión terminó por inactividad. Inicia sesión nuevamente.'];

            return;
        }

        $_SESSION['_ultima_actividad'] = time();
    }
}
