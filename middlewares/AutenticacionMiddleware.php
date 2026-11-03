<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\SesionDAO;
use App\Configuracion\Herramientas;

/** Controla las rutas que exigen una sesión activa. */
final class AutenticacionMiddleware
{
    /** Comprueba la sesión persistida y actualiza su vencimiento por inactividad. */
    public static function validarSesionPersistida(?array $usuario, SesionDAO $sesionDAO): ?array
    {
        if ($usuario === null) {
            return null;
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);
        $identificadorSesion = session_id();

        if (
            $usuarioId > 0
            && $identificadorSesion !== ''
            && $sesionDAO->renovarSiVigente($usuarioId, $identificadorSesion)
        ) {
            return $usuario;
        }

        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        Herramientas::mostrarAviso(
            'error',
            'La sesión venció o fue revocada. Inicia sesión nuevamente.'
        );

        return null;
    }

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
