<?php
declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\VentaDAO;
use DomainException;
use InvalidArgumentException;

/**
 * Capa de Servicio para Evaluación de Solicitudes de Crédito, Ventas y Abonos.
 */
final class EvaluacionCreditoServicio
{
    public function evaluarSolicitudCredito(PedidoDAO $pedidoDAO, array $entrada): string
    {
        $id       = (int) ($entrada['pedido_id'] ?? 0);
        $decision = (string) ($entrada['decision'] ?? '');

        if ($id < 1) {
            throw new InvalidArgumentException('No se encontró la solicitud de crédito.');
        }

        $pedidoDAO->evaluarSolicitudCredito($id, $decision);

        if ($decision === 'aprobar') {
            return 'Solicitud marcada como aprobada. Registra la venta y el cronograma desde Ventas.';
        }

        return 'Solicitud marcada como no aprobada.';
    }

    public function procesarRegistroVenta(VentaDAO $ventaDAO, array $entrada, int $usuarioId): array
    {
        $clienteId        = filter_var($entrada['cliente_id'] ?? null, FILTER_VALIDATE_INT);
        $unidadId         = filter_var($entrada['unidad_id'] ?? null, FILTER_VALIDATE_INT);
        $modalidad        = (string) ($entrada['modalidad'] ?? '');
        $descuento        = filter_var($entrada['descuento'] ?? 0, FILTER_VALIDATE_FLOAT);
        $inicial          = filter_var($entrada['inicial'] ?? 0, FILTER_VALIDATE_FLOAT);
        $cuotas           = filter_var($entrada['numero_cuotas'] ?? 0, FILTER_VALIDATE_INT);
        $fechaVencimiento = trim((string) ($entrada['fecha_primera_cuota'] ?? ''));

        $esCredito = ($modalidad === 'Crédito');

        if (!$clienteId || $clienteId < 1 || !$unidadId || $unidadId < 1 || $usuarioId < 1) {
            throw new DomainException('Selecciona un cliente activo y una unidad disponible.');
        }

        if (
            !in_array($modalidad, ['Contado', 'Crédito'], true)
            || $descuento === false
            || !$this->esImporteValido($entrada['descuento'] ?? 0)
        ) {
            throw new DomainException('Revisa la modalidad de venta y el descuento.');
        }

        if ($esCredito) {
            if (
                $inicial === false
                || !$this->esImporteValido($entrada['inicial'] ?? 0)
                || $cuotas === false
                || $cuotas < 1
                || $cuotas > 36
            ) {
                throw new DomainException('Si es a crédito, revisa la cuota inicial y el número de cuotas (1 a 36).');
            }

            if (!$this->esFechaValida($fechaVencimiento) || $fechaVencimiento < date('Y-m-d')) {
                throw new DomainException('Selecciona una fecha válida para la primera cuota.');
            }
        }

        $datosVenta = [
            'cliente_id'          => (int) $clienteId,
            'unidad_id'           => (int) $unidadId,
            'usuario_id'          => $usuarioId,
            'modalidad'           => $modalidad,
            'descuento'           => (float) $descuento,
            'inicial'             => $esCredito ? (float) $inicial : 0.0,
            'numero_cuotas'       => $esCredito ? (int) $cuotas : 0,
            'fecha_primera_cuota' => $esCredito ? $fechaVencimiento : '',
        ];

        $idVenta = $ventaDAO->crear($datosVenta);

        return [
            'venta_id'   => $idVenta,
            'es_credito' => $esCredito,
        ];
    }

    public function procesarPagoCuota(VentaDAO $ventaDAO, array $entrada): void
    {
        $montoTexto = trim((string) ($entrada['monto'] ?? ''));
        $monto = filter_var($montoTexto, FILTER_VALIDATE_FLOAT);
        $id    = filter_var($entrada['cuota_id'] ?? null, FILTER_VALIDATE_INT);

        if (
            !$id
            || $id < 1
            || $monto === false
            || $monto <= 0
            || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $montoTexto)
        ) {
            throw new DomainException('Selecciona una cuota y un monto válido de hasta dos decimales.');
        }

        $ventaDAO->pagarCuota((int) $id, (float) $monto);
    }

    private function esFechaValida(string $valor): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return false;
        }

        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }

    private function esImporteValido(mixed $valor): bool
    {
        if (!is_string($valor) && !is_int($valor) && !is_float($valor)) {
            return false;
        }

        return preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', trim((string) $valor)) === 1;
    }
}
