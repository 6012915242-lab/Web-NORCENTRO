<?php

declare(strict_types=1);

namespace App\Nucleo;

use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\SesionDAO;
use App\AccesoDatos\UsuarioDAO;
use App\AccesoDatos\VentaDAO;
use App\Configuracion\Herramientas;
use App\Controladores\ClienteControlador;
use App\Controladores\MotoControlador;
use App\Controladores\OfertaControlador;
use App\Controladores\PedidoControlador;
use App\Controladores\SesionControlador;
use App\Controladores\VentaControlador;

/** Envía cada acción POST al controlador correspondiente. */
final class DespachadorAcciones
{
    public function __construct(
        private UsuarioDAO $usuarioDAO,
        private MotoDAO $motoDAO,
        private ClienteDAO $clienteDAO,
        private VentaDAO $ventaDAO,
        private OfertaDAO $ofertaDAO,
        private PedidoDAO $pedidoDAO,
        private SesionDAO $sesionDAO
    ) {}

    /** Construye el despachador con los DAO que comparte la petición. */
    public static function desdeContenedor(ContenedorDatos $datos): self
    {
        return new self(
            $datos->usuarios,
            $datos->motos,
            $datos->clientes,
            $datos->ventas,
            $datos->ofertas,
            $datos->pedidos,
            $datos->sesiones
        );
    }

    public function despachar(
        string $accion,
        array $datos,
        array $archivos,
        array $usuarioActual
    ): void {
        switch ($accion) {
            case 'cerrar_sesion':
                (new SesionControlador($this->usuarioDAO, $this->sesionDAO))->cerrarSesion();
                break;

            case 'guardar_moto':
                (new MotoControlador($this->motoDAO))->guardar(
                    $datos,
                    $archivos,
                    (int) ($datos['id'] ?? 0) > 0
                );
                break;

            case 'desactivar_moto':
                (new MotoControlador($this->motoDAO))->desactivar((int) ($datos['id'] ?? 0));
                break;

            case 'agregar_unidad_inventario':
                (new MotoControlador($this->motoDAO))->agregarUnidadInventario($datos);
                break;

            case 'asignar_color_unidad':
                (new MotoControlador($this->motoDAO))->asignarColorUnidad($datos);
                break;

            case 'guardar_cliente':
                (new ClienteControlador($this->clienteDAO))->guardar(
                    $datos,
                    (int) ($datos['id'] ?? 0) > 0
                );
                break;

            case 'desactivar_cliente':
                (new ClienteControlador($this->clienteDAO))->desactivar((int) ($datos['id'] ?? 0));
                break;

            case 'guardar_oferta':
                (new OfertaControlador($this->ofertaDAO))->guardar($datos);
                break;

            case 'desactivar_oferta':
                (new OfertaControlador($this->ofertaDAO))->desactivar((int) ($datos['id'] ?? 0));
                break;

            case 'crear_venta':
                (new VentaControlador($this->ventaDAO))->crear(
                    $datos,
                    (int) $usuarioActual['id']
                );
                break;

            case 'registrar_pago_cuota':
                (new VentaControlador($this->ventaDAO))->pagar($datos);
                break;

            case 'evaluar_solicitud_credito':
                (new PedidoControlador($this->pedidoDAO))->evaluarCredito($datos);
                break;

            default:
                Herramientas::mostrarAviso('error', 'La acción solicitada no existe.');
        }
    }
}
