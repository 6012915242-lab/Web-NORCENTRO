<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Crédito asociado a una venta y su saldo vigente. */
final class Credito implements EntidadORM
{
    public function __construct(
        public int $id,
        public int $ventaId,
        public float $inicial,
        public float $montoFinanciado,
        public int $numeroCuotas,
        public float $saldo,
        public string $estado,
        public string $creadoEn
    ) {
    }

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (int) ($registro['venta_id'] ?? 0),
            (float) ($registro['inicial'] ?? 0),
            (float) ($registro['monto_financiado'] ?? 0),
            (int) ($registro['numero_cuotas'] ?? 0),
            (float) ($registro['saldo'] ?? 0),
            (string) ($registro['estado'] ?? 'Activo'),
            (string) ($registro['creado_en'] ?? '')
        );
    }
}
