<?php

declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\EntidadORM;
use DateTimeImmutable;

/** Usuario autenticable con su rol y estado de seguridad. */
final class Usuario implements EntidadORM
{
    public function __construct(
        public int $id,
        public int $rolId,
        public string $usuario,
        public string $passwordHash,
        public string $nombre,
        public string $rol,
        public string $estado,
        public int $intentosFallidos = 0,
        public ?string $bloqueadoHasta = null,
        public ?bool $bloqueadoActual = null
    ) {}

    public static function desdeRegistro(array $registro): self
    {
        return new self(
            (int) ($registro['id'] ?? 0),
            (int) ($registro['rol_id'] ?? 0),
            (string) ($registro['usuario'] ?? ''),
            (string) ($registro['password_hash'] ?? ''),
            (string) ($registro['nombre'] ?? ''),
            (string) ($registro['rol'] ?? ''),
            (string) ($registro['estado'] ?? 'Inactivo'),
            (int) ($registro['intentos_fallidos'] ?? 0),
            isset($registro['bloqueado_hasta']) ? (string) $registro['bloqueado_hasta'] : null,
            isset($registro['bloqueado_actual']) ? (bool) $registro['bloqueado_actual'] : null
        );
    }

    public function estaBloqueado(): bool
    {
        if ($this->bloqueadoActual !== null) {
            return $this->bloqueadoActual;
        }

        if ($this->bloqueadoHasta === null || $this->bloqueadoHasta === '') {
            return false;
        }

        return new DateTimeImmutable($this->bloqueadoHasta) > new DateTimeImmutable;
    }
}
