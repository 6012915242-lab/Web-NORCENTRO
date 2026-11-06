<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\AccesoDatos\AuditoriaDAO;
use App\AccesoDatos\PermisoDAO;
use App\Configuracion\Herramientas;
use App\Nucleo\MotorVistas;
use Throwable;

/** Protege acciones y páginas usando permisos almacenados en la base. */
final class AutorizacionRbacMiddleware
{
    private const PERMISOS_POR_ACCION = [
        'guardar_moto' => 'motos.gestionar',
        'desactivar_moto' => 'motos.gestionar',
        'agregar_unidad_inventario' => 'inventario.gestionar',
        'asignar_color_unidad' => 'inventario.gestionar',
        'guardar_cliente' => 'clientes.gestionar',
        'desactivar_cliente' => 'clientes.desactivar',
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

    public function __construct(
        private PermisoDAO $permisoDAO,
        private ?AuditoriaDAO $auditoriaDAO = null
    ) {}

    /** Devuelve los permisos vigentes para controlar las opciones de la interfaz. */
    public function permisosDeUsuario(int $usuarioId): array
    {
        if (! array_key_exists($usuarioId, $this->permisosUsuario)) {
            $this->permisosUsuario[$usuarioId] = $this->permisoDAO->codigosPorUsuario($usuarioId);
        }

        return $this->permisosUsuario[$usuarioId];
    }

    public function verificarPermisoAccion(string $accion, ?array $usuario): void
    {
        if ($accion === 'cerrar_sesion') {
            return;
        }

        $permiso = self::PERMISOS_POR_ACCION[$accion] ?? null;

        if ($permiso === null) {
            $this->denegar($usuario === null, $usuario, $accion);
        }

        $this->exigirPermiso($usuario, $permiso, $accion);
    }

    public function verificarAccesoPagina(string $pagina, ?array $usuario): void
    {
        $permiso = self::PERMISOS_POR_PAGINA[$pagina] ?? null;

        if ($permiso !== null) {
            $this->exigirPermiso($usuario, $permiso, $pagina);
        }
    }

    private function exigirPermiso(?array $usuario, string $codigoPermiso, string $recurso): void
    {
        if ($usuario === null) {
            $this->denegar(true);
        }

        $usuarioId = (int) ($usuario['id'] ?? 0);

        if ($usuarioId > 0 && in_array($codigoPermiso, $this->permisosDeUsuario($usuarioId), true)) {
            return;
        }

        $this->denegar(false, $usuario, $recurso);
    }

    private function denegar(bool $requiereSesion, ?array $usuario = null, string $recurso = ''): never
    {
        if ($requiereSesion) {
            http_response_code(401);
            Herramientas::mostrarAviso('error', 'Inicia sesión para acceder.');
            Herramientas::redirigir('login');
        }

        try {
            $this->auditoriaDAO?->registrar([
                'usuario_id' => $usuario['id'] ?? null,
                'accion' => 'acceso_denegado',
                'recurso' => mb_substr($recurso, 0, 120),
                'metodo_http' => mb_substr((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'), 0, 10),
                'direccion_ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'resultado' => 'Denegado',
                'datos' => json_encode(['recurso' => $recurso], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Throwable) {
            // Un fallo de auditoría no debe habilitar ni ocultar el rechazo.
        }

        http_response_code(403);
        $pagina = 'acceso_denegado';
        $titulo = 'Acceso denegado | Norcentro';
        $usuarioActual = $usuario;
        $mensajeError = '';
        $aviso = null;
        $solicitudesCreditoPendientes = 0;
        $permisosActuales = $usuario !== null
            ? $this->permisosDeUsuario((int) ($usuario['id'] ?? 0))
            : [];
        $usuarioPuede = static fn (string $codigo): bool => in_array($codigo, $permisosActuales, true);
        $vista = dirname(__DIR__).'/vistas/paginas/acceso_denegado.php';

        echo (new MotorVistas(dirname(__DIR__).'/vistas'))->renderizar($pagina, compact(
            'pagina',
            'titulo',
            'usuarioActual',
            'mensajeError',
            'aviso',
            'solicitudesCreditoPendientes',
            'permisosActuales',
            'usuarioPuede',
            'vista'
        ));
        exit;
    }
}
