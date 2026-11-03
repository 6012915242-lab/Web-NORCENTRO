<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\VentaDAO;
use App\Configuracion\Herramientas;
use App\Servicios\EvaluacionCreditoServicio;
use DomainException;

final class VentaControlador
{
    private EvaluacionCreditoServicio $servicioCredito;

    public function __construct(
        private VentaDAO $dao,
        ?EvaluacionCreditoServicio $servicioCredito = null
    ) {
        $this->servicioCredito = $servicioCredito ?? new EvaluacionCreditoServicio();
    }

    public function crear(array $post, int $usuarioId): never
    {
        $clienteId = filter_var($post['cliente_id'] ?? 0, FILTER_VALIDATE_INT);

        try {
            $resultado = $this->servicioCredito->procesarRegistroVenta($this->dao, $post, $usuarioId);
            $idVenta   = (int) $resultado['venta_id'];

            if ($resultado['es_credito']) {
                Herramientas::mostrarAviso(
                    'success',
                    'Venta ' . $idVenta . ' confirmada. Se generó el cronograma; desde aquí puedes registrar cada abono y consultar el saldo actualizado.'
                );
                Herramientas::redirigir('creditos', ['venta_id' => $idVenta]);
            }

            Herramientas::mostrarAviso('success', 'Venta ' . $idVenta . ' confirmada. La unidad pasó a estado Vendida.');
            Herramientas::redirigir('ventas');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
            Herramientas::redirigir('ventas', ($clienteId && $clienteId > 0) ? ['cliente_id' => $clienteId] : []);
        }
    }

    public function pagar(array $post): never
    {
        try {
            $this->servicioCredito->procesarPagoCuota($this->dao, $post);
            Herramientas::mostrarAviso('success', 'Pago registrado y saldo actualizado.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('creditos');
    }
}
