<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Representa una cuota de crédito. */
final class Cuota implements EntidadORM
{
    public function __construct(
        public int $id, public int $creditoId, public int $numero,
        public string $fechaVencimiento, public float $monto, public float $pagado, public string $estado
    ) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self((int) $registro['id'], (int) $registro['credito_id'], (int) $registro['numero'],
            (string) $registro['fecha_vencimiento'], (float) $registro['monto'],
            (float) $registro['pagado'], (string) $registro['estado']);
    }

    public function aRegistro(): array
    {
        return ['id' => $this->id, 'credito_id' => $this->creditoId, 'numero' => $this->numero,
            'fecha_vencimiento' => $this->fechaVencimiento, 'monto' => $this->monto,
            'pagado' => $this->pagado, 'estado' => $this->estado];
    }
}
