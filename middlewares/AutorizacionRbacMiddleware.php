<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Configuracion\Herramientas;

/** Define el acceso RBAC de acciones y páginas privadas. */
final class AutorizacionRbacMiddleware
{
    public static function verificarPermisoAccion(string $accion, ?array $usuario): void
    {
        $accionesAdministrador = ['guardar_moto', 'desactivar_moto', 'guardar_oferta', 'desactivar_oferta', 'agregar_unidad_inventario', 'desactivar_cliente'];
        if (in_array($accion, $accionesAdministrador, true)) {
            self::exigirRol($usuario, ['Administrador']);
        }
        if ($accion === 'evaluar_solicitud_credito') {
            self::exigirRol($usuario, ['Administrador', 'Vendedor']);
        }
    }

    public static function verificarAccesoPagina(string $pagina, ?array $usuario): void
    {
        if ($pagina === 'motos' || $pagina === 'ofertas') {
            self::exigirRol($usuario, ['Administrador']);
        }
    }

    private static function exigirRol(?array $usuario, array $rolesPermitidos): void
    {
        $rolActual = (string)($usuario['rol'] ?? '');
        if ($usuario !== null && in_array($rolActual, $rolesPermitidos, true)) {
            return;
        }
        http_response_code($usuario === null ? 401 : 403);
        Herramientas::mostrarAviso('error', $usuario === null ? 'Inicia sesión para acceder.' : 'Tu rol no tiene permisos para esta acción.');
        Herramientas::redirigir($usuario === null ? 'login' : 'dashboard');
    }
}
