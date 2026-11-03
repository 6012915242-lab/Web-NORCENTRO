<?php
declare(strict_types = 1);
namespace App\Modelos;
/** Representa la información de un modelo de motocicleta. */
final class Moto
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
        public string $estado) {
    }
}
