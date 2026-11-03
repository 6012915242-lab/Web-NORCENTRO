<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\PedidoDAO;
use App\Configuracion\Herramientas;
use App\Servicios\EvaluacionCreditoServicio;
use DomainException;
use InvalidArgumentException;

/** Coordina pedidos, el checkout y la evaluación interna de crédito. */
final class PedidoControlador
{
    private EvaluacionCreditoServicio $servicioCredito;

    public function __construct(
        private PedidoDAO $pedidoDAO,
        ?EvaluacionCreditoServicio $servicioCredito = null
    ) {
        $this->servicioCredito = $servicioCredito ?? new EvaluacionCreditoServicio();
    }

    public function evaluarCredito(array $post): never
    {
        try {
            $mensaje = $this->servicioCredito->evaluarSolicitudCredito($this->pedidoDAO, $post);
            Herramientas::mostrarAviso('success', $mensaje);
        } catch (DomainException|InvalidArgumentException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('creditos');
    }
}
