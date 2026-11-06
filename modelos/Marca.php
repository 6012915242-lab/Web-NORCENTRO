<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Representa una marca de motocicletas. */
final class Marca implements EntidadORM
{
    public function __construct(public int $id, public string $nombre, public string $estado) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self((int) $registro['id'], (string) $registro['nombre'], (string) $registro['estado']);
    }
}
