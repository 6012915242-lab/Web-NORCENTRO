<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;

/** Representa los datos principales de un cliente. */
final class Cliente implements EntidadORM
{
    public function __construct(
        public int $id,
        public string $dni,
        public string $nombres,
        public string $apellidos,
        public string $telefono,
        public string $direccion,
        public string $correo,
        public string $estado
    ) {
    }

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (string) ($registro['dni'] ?? ''),
            (string) ($registro['nombres'] ?? ''),
            (string) ($registro['apellidos'] ?? ''),
            (string) ($registro['telefono'] ?? ''),
            (string) ($registro['direccion'] ?? ''),
            (string) ($registro['correo'] ?? ''),
            (string) ($registro['estado'] ?? 'Activo')
        );
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
}
