<?php
declare(strict_types = 1);
namespace App\Modelos;
/** Representa una promoción asociada a una motocicleta. */
final class Oferta
{
    public function __construct(
        public int $id,
        public int $idMoto,
        public string $titulo,
        public string $descripcion,
        public float $precioNormal,
        public float $precioOferta,
        public string $inicio,
        public string $fin,
        public string $estado) {
    }
}
