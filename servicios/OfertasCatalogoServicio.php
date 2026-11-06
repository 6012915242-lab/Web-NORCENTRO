<?php

declare(strict_types=1);

namespace App\Servicios;

/** Organiza las ofertas activas para mostrar una por motocicleta. */
final class OfertasCatalogoServicio
{
    public function agruparPorMoto(array $ofertas): array
    {
        $ofertasPorMoto = [];

        foreach ($ofertas as $oferta) {
            $motoId = (int) $oferta['moto_id'];

            if (!isset($ofertasPorMoto[$motoId])) {
                $ofertasPorMoto[$motoId] = $oferta;
            }
        }

        return $ofertasPorMoto;
    }
}
