<?php

declare(strict_types=1);

namespace App\Servicios;

use DomainException;

/** Validaciones reutilizables de datos de entrada. */
final class Validador
{
    public static function dni(string $valor, string $mensaje = 'El DNI debe tener 8 dígitos.'): void
    {
        if (! preg_match('/^\d{8}$/', $valor)) {
            throw new DomainException($mensaje);
        }
    }

    public static function telefono(string $valor, string $mensaje = 'El teléfono debe tener 9 dígitos y comenzar con 9.'): void
    {
        if (! preg_match('/^9\d{8}$/', $valor)) {
            throw new DomainException($mensaje);
        }
    }

    /** Conserva el formato de teléfono aceptado por los formularios existentes. */
    public static function telefonoFlexible(string $valor, string $mensaje): void
    {
        if (! preg_match('/^[+0-9 ()-]{7,20}$/', $valor)) {
            throw new DomainException($mensaje);
        }
    }

    public static function correo(string $valor, string $mensaje = 'Ingresa un correo válido.'): void
    {
        if (! filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException($mensaje);
        }
    }

    public static function textoRequerido(string $valor, int $maximo, string $mensaje = 'Ingresa un texto válido.'): void
    {
        if (trim($valor) === '' || mb_strlen($valor) > $maximo) {
            throw new DomainException($mensaje);
        }
    }

    public static function montoPositivo(float $valor, string $mensaje = 'Ingresa un monto positivo.'): void
    {
        if (! is_finite($valor) || $valor <= 0) {
            throw new DomainException($mensaje);
        }
    }
}
