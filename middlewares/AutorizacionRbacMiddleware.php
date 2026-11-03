<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\PermisoDAO;
use App\Configuracion\Herramientas;

/** Protege acciones y páginas usando permisos almacenados en la base. */
final class AutorizacionRbacMiddleware
{
    private const PERMISOS_POR_ACCION = [
        'guardar_moto' => 'motos.gestionar',
        'desactivar_moto' => 'motos.gestionar',
        'agregar_unidad_inventario' => 'inventario.gestionar',
        'guardar_cliente' => 'clientes.gestionar',
        'desactivar_cliente' => 'clientes.gestionar',
        'guardar_oferta' => 'ofertas.gestionar',
        'desactivar_oferta' => 'ofertas.gestionar',
        'crear_venta' => 'ventas.registrar',
        'registrar_pago_cuota' => 'creditos.revisar',
        'evaluar_solicitud_credito' => 'creditos.revisar',
    ];

    private const PERMISOS_POR_PAGINA = [
        'dashboard' => 'dashboard.ver',
        'motos' => 'motos.gestionar',
        'inventario' => 'inventario.gestionar',
        'clientes' => 'clientes.gestionar',
        'ventas' => 'ventas.registrar',
        'creditos' => 'creditos.revisar',
    ];

    private array $permisosUsuario = [];

    public function __construct(private PermisoDAO $permisoDAO)
    {
    }

    public function verificarPermisoAccion(string $accion, ?array $usuario): void
    {
        $permiso = self::PERMISOS_POR_ACCION[$accion] ?? null;

        if ($permiso !== null) {
            $this->exigirPermiso($usuario, $permiso);
        }
    }

    public function verificarAccesoPagina(string $pagina, ?array $usuario): void
    {
        $permiso = self::PERMISOS_POR_PAGINA[$pagina] ?? null;

        if ($permiso !== null) {
            $this->exigirPermiso($usuario, $permiso);
        }
    }

    private function exigirPermiso(?array $usuario, string $codigoPermiso): void
    {
        if ($usuario === null) {
            $this->denegar(true);
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);

        if ($usuarioId > 0 && $this->tienePermiso($usuarioId, $codigoPermiso)) {
            return;
        }

        $this->denegar(false);
    }

    private function tienePermiso(int $usuarioId, string $codigoPermiso): bool
    {
        if (!array_key_exists($usuarioId, $this->permisosUsuario)) {
            $this->permisosUsuario[$usuarioId] = $this->permisoDAO->codigosPorUsuario($usuarioId);
        }

        return in_array($codigoPermiso, $this->permisosUsuario[$usuarioId], true);
    }

    private function denegar(bool $requiereSesion): never
    {
        http_response_code($requiereSesion ? 401 : 403);
        Herramientas::mostrarAviso(
            'error',
            $requiereSesion
                ? 'Inicia sesión para acceder.'
                : 'Tu usuario no tiene el permiso necesario para esta operación.'
        );
        Herramientas::redirigir($requiereSesion ? 'login' : 'dashboard');
    }
}
