<?php

declare(strict_types=1);

namespace App\Servicios;

use DomainException;

/** Normaliza campos de texto sin aceptar estructuras inesperadas. */
final class TextoEntrada
{
    public static function normalizar(mixed $valor): string
    {
        if (! is_string($valor) && ! is_int($valor) && ! is_float($valor)) {
            throw new DomainException('Los campos deben contener texto válido.');
        }

        return trim((string) $valor);
    }
}
