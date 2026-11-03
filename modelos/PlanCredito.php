<?php
declare(strict_types=1);

namespace App\Modelos;

use DomainException;

/** Calcula el recargo único y distribuye las cuotas sin perder centavos. */
final class PlanCredito
{
    public const PORCENTAJE_RECARGO = 5;

    public static function calcular(float $precioVenta, float $inicial, int $numeroCuotas): array
    {
        $precioCentavos = (int) round($precioVenta * 100);
        $inicialCentavos = (int) round($inicial * 100);
        $capitalCentavos = $precioCentavos - $inicialCentavos;

        if ($inicialCentavos < 0 || $capitalCentavos <= 0 || $numeroCuotas < 1 || $numeroCuotas > 36) {
            throw new DomainException('La inicial debe ser menor al precio y el crédito debe tener entre 1 y 36 cuotas.');
        }

        $recargoCentavos = intdiv($capitalCentavos * self::PORCENTAJE_RECARGO + 50, 100);
        $financiadoCentavos = $capitalCentavos + $recargoCentavos;
        $cuotaBase = intdiv($financiadoCentavos, $numeroCuotas);
        if ($cuotaBase < 1) {
            throw new DomainException('El monto financiado es demasiado pequeño para el número de cuotas.');
        }

        $centavosRestantes = $financiadoCentavos % $numeroCuotas;
        $cuotas = [];
        for ($numero = 1; $numero <= $numeroCuotas; $numero++) {
            $cuotas[] = ($cuotaBase + ($numero <= $centavosRestantes ? 1 : 0)) / 100;
        }

        return [
            'capital' => $capitalCentavos / 100,
            'recargo' => $recargoCentavos / 100,
            'financiado' => $financiadoCentavos / 100,
            'cuotas' => $cuotas,
        ];
    }
}
