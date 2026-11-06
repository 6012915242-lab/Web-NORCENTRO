<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Marca de motocicletas (tabla marcas). Una Moto pertenece a una Marca (relación N:1). */
final class Marca implements EntidadORM
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $estado
    ) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (string) ($registro['nombre'] ?? ''),
            (string) ($registro['estado'] ?? 'Activo')
        );
    }
}
