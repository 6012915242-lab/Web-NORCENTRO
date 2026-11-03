<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Configuracion\Herramientas;

/** Controla las rutas que exigen una sesión activa. */
final class AutenticacionMiddleware
{
    public static function exigirSesionParaAccion(?array $usuario): void
    {
        if ($usuario !== null) {
            return;
        }
        http_response_code(401);
        Herramientas::mostrarAviso('error', 'Inicia sesión para realizar esta acción.');
        Herramientas::redirigir('login');
    }

    public static function protegerRutaPrivada(string $pagina, ?array $usuario): void
    {
        $paginasPublicas = ['inicio', 'catalogo', 'ofertas', 'contacto', 'login', 'moto', 'compra', 'compra_credito'];
        if (in_array($pagina, $paginasPublicas, true)) {
            return;
        }
        self::exigirSesionParaAccion($usuario);
    }
}
