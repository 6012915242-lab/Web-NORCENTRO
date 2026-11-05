<?php
declare(strict_types=1);

namespace App\Modelos;

use DomainException;
use DateTimeImmutable;

/** Calcula el recargo único y distribuye las cuotas sin perder centavos. */
final class PlanCredito
{
    public const PORCENTAJE_RECARGO = 5;

    public static function fechasCuotas(DateTimeImmutable $primeraFecha, int $numeroCuotas): array
    {
        if ($numeroCuotas < 1 || $numeroCuotas > 36) {
            throw new DomainException('El crédito debe tener entre 1 y 36 cuotas.');
        }

        $diaOriginal = (int) $primeraFecha->format('j');
        $primerMes = $primeraFecha->modify('first day of this month');
        $fechas = [];

        for ($indice = 0; $indice < $numeroCuotas; $indice++) {
            $mes = $primerMes->modify('+' . $indice . ' months');
            $dia = min($diaOriginal, (int) $mes->format('t'));
            $fechas[] = $mes->setDate((int) $mes->format('Y'), (int) $mes->format('n'), $dia);
        }

        return $fechas;
    }

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
