<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Venta confirmada de una unidad de motocicleta. */
final class Venta implements EntidadORM
{
    public function __construct(
        public int $id,
        public int $clienteId,
        public int $usuarioId,
        public int $unidadId,
        public float $precio,
        public float $descuento,
        public float $total,
        public string $modalidad,
        public string $estado,
        public string $fecha,
        public ?Cliente $cliente = null,
        public ?Moto $moto = null
    ) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (int) ($registro['cliente_id'] ?? 0),
            (int) ($registro['usuario_id'] ?? 0),
            (int) ($registro['unidad_id'] ?? 0),
            (float) ($registro['precio'] ?? 0),
            (float) ($registro['descuento'] ?? 0),
            (float) ($registro['total'] ?? 0),
            (string) ($registro['modalidad'] ?? 'Contado'),
            (string) ($registro['estado'] ?? 'Confirmada'),
            (string) ($registro['fecha'] ?? '')
        );
    }
}
