<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Configuracion\Herramientas;

/** Inicializa la sesión y aplica saneamiento y CSRF de forma centralizada. */
final class SeguridadMiddleware
{
    public static function inicializarEntornoSeguro(string $rutaSesion): void
    {
        if (!is_dir($rutaSesion)) {
            @mkdir($rutaSesion, 0770, true);
        }
        if (is_dir($rutaSesion) && is_writable($rutaSesion)) {
            session_save_path($rutaSesion);
        }
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function sanearArregloEntrada(array $entrada): array
    {
        $resultado = [];
        foreach ($entrada as $clave => $valor) {
            $claveSegura = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$clave);
            if ($claveSegura === '') {
                continue;
            }
            $resultado[$claveSegura] = is_array($valor)
                ? self::sanearArregloEntrada($valor)
                : trim((string)$valor);
        }
        return $resultado;
    }

    public static function validarPeticionPostCsrf(): void
    {
        Herramientas::verificarCsrf();
    }
}
