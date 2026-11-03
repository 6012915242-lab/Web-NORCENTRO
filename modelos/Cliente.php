<?php
declare(strict_types = 1);
namespace App\Modelos;
/** Representa los datos principales de un cliente. */
final class Cliente
{
    public function __construct(
        public int $id,
        public string $dni,
        public string $nombres,
        public string $apellidos,
        public string $telefono,
        public string $direccion,
        public string $correo,
        public string $estado) {
    }
}
