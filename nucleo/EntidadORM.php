<?php

declare(strict_types=1);

namespace App\Nucleo;

/** Contrato para convertir registros de la base de datos en entidades. */
interface EntidadORM
{
    public static function desdeRegistro(array $registro): self;
}
