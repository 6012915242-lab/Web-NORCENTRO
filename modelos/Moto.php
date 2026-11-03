<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Representa la información de un modelo de motocicleta. */
final class Moto implements EntidadORM
{
    public function __construct(
        public int $id,
        public int $marcaId,
        public string $marca,
        public string $modelo,
        public int $cilindrada,
        public string $color,
        public float $precio,
        public string $descripcion,
        public int $anio,
        public string $imagen,
        public string $estado
    ) {
    }

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (int) ($registro['marca_id'] ?? 0),
            (string) ($registro['marca'] ?? ''),
            (string) ($registro['modelo'] ?? ''),
            (int) ($registro['cilindrada'] ?? 0),
            (string) ($registro['color'] ?? ''),
            (float) ($registro['precio'] ?? 0),
            (string) ($registro['descripcion'] ?? ''),
            (int) ($registro['anio'] ?? 0),
            (string) ($registro['imagen'] ?? ''),
            (string) ($registro['estado'] ?? 'Activo')
        );
    }
}
