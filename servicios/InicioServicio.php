<?php

declare(strict_types=1);

namespace App\Servicios;

/** Selecciona los modelos y la promoción que se muestran en la portada. */
final class InicioServicio
{
    public function seleccionarDestacados(array $motos, ?array $motoPrincipal, array $ofertas): array
    {
        $motosDestacadas = [];
        $modelosPreferidos = ['YZF-R15 ABS', 'MT-09 ABS', 'Pulsar NS200', 'Duke 200'];

        foreach ($modelosPreferidos as $modelo) {
            foreach ($motos as $moto) {
                if (
                    ($moto['modelo'] ?? '') !== $modelo
                    || (int) ($moto['id'] ?? 0) === (int) ($motoPrincipal['id'] ?? -1)
                ) {
                    continue;
                }

                $motosDestacadas[] = $moto;
                /** Conserva la primera coincidencia según el orden de preferencias. */
                break 2;
            }
        }

        $ofertaDestacada = null;
        $mayorDescuento = -1.0;

        foreach ($ofertas as $oferta) {
            $precioNormal = (float) ($oferta['precio_normal'] ?? 0);
            $precioOferta = (float) ($oferta['precio_oferta'] ?? 0);

            if ($precioNormal <= 0 || $precioOferta <= 0 || $precioOferta >= $precioNormal) {
                continue;
            }

            /** Compara descuentos relativos para elegir la oferta destacada. */
            $descuento = ($precioNormal - $precioOferta) / $precioNormal;
            if ($descuento > $mayorDescuento) {
                $mayorDescuento = $descuento;
                $ofertaDestacada = $oferta;
            }
        }

        return [
            'motosDestacadas' => $motosDestacadas,
            'ofertaDestacada' => $ofertaDestacada,
        ];
    }
}
