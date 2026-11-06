<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Pedido web para compra al contado o solicitud de crédito. */
final class Pedido implements EntidadORM
{
    public function __construct(
        public int $id,
        public string $requestId,
        public string $nombres,
        public string $apellidos,
        public string $dni,
        public string $modalidad,
        public float $total,
        public string $estado,
        public string $creadoEn
    ) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (string) ($registro['request_id'] ?? ''),
            (string) ($registro['nombres'] ?? ''),
            (string) ($registro['apellidos'] ?? ''),
            (string) ($registro['dni'] ?? ''),
            (string) ($registro['modalidad'] ?? 'Contado'),
            (float) ($registro['total'] ?? 0),
            (string) ($registro['estado'] ?? ''),
            (string) ($registro['creado_en'] ?? '')
        );
    }
}
