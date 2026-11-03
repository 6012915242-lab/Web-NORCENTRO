<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Representa una promoción asociada a una motocicleta. */
final class Oferta implements EntidadORM
{
    public function __construct(
        public int $id,
        public int $idMoto,
        public string $titulo,
        public string $descripcion,
        public float $precioNormal,
        public float $precioOferta,
        public string $inicio,
        public string $fin,
        public string $estado
    ) {
    }

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (int) ($registro['moto_id'] ?? 0),
            (string) ($registro['titulo'] ?? ''),
            (string) ($registro['descripcion'] ?? ''),
            (float) ($registro['precio_normal'] ?? 0),
            (float) ($registro['precio_oferta'] ?? 0),
            (string) ($registro['fecha_inicio'] ?? ''),
            (string) ($registro['fecha_fin'] ?? ''),
            (string) ($registro['estado'] ?? 'Activa')
        );
    }

    public function porcentajeDescuento(): float
    {
        if ($this->precioNormal <= 0) {
            return 0;
        }

        return round((1 - ($this->precioOferta / $this->precioNormal)) * 100, 2);
    }
}
