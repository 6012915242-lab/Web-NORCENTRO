<?php
declare(strict_types = 1);
namespace App\Controladores;
use App\Configuracion\Herramientas;
use App\AccesoDatos\PedidoDAO;
/** Coordina pedidos, el checkout y la evaluación interna de crédito. */
final class PedidoControlador
{
    public function __construct(private PedidoDAO $dao) {}

    public function evaluarCredito(array $post): never
    {
        $id = (int)($post['pedido_id'] ?? 0);
        $decision = (string)($post['decision'] ?? '');
        if ($id < 1) {
            Herramientas::mostrarAviso('error', 'No se encontró la solicitud de crédito.');
            Herramientas::redirigir('creditos');
        }
        try {
            $this->dao->evaluarSolicitudCredito($id, $decision);
            Herramientas::mostrarAviso('success', $decision === 'aprobar'?'Solicitud marcada como aprobada. Registra la venta y el cronograma desde Ventas.':'Solicitud marcada como no aprobada.');
        } catch (\DomainException|\InvalidArgumentException $e) {
            Herramientas::mostrarAviso('error', $e->getMessage());
        }
        Herramientas::redirigir('creditos');
    }
}
