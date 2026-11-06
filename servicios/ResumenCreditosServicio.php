<?php

declare(strict_types=1);

namespace App\Servicios;

/** Calcula los indicadores de la cartera de créditos (activos, vencidos y saldo pendiente). */
final class ResumenCreditosServicio
{
    public function calcular(array $creditos, array $cuotas, array $solicitudes): array
    {
        $resumen = [
            'creditosPendientes' => 0,
            'cantidadCreditosActivos' => 0,
            'cantidadCuotasVencidas' => 0,
            'saldoCreditoActivo' => 0.0,
        ];

        foreach ($solicitudes as $solicitud) {
            if ($solicitud['estado'] === 'En revisión de crédito') {
                $resumen['creditosPendientes']++;
            }
        }

        foreach ($creditos as $credito) {
            if ($credito['estado'] === 'Activo' && (float) $credito['saldo'] > 0) {
                $resumen['cantidadCreditosActivos']++;
                $resumen['saldoCreditoActivo'] += (float) $credito['saldo'];
            }

            foreach ($cuotas[$credito['id']] ?? [] as $cuota) {
                if ($cuota['estado'] === 'Vencida') {
                    $resumen['cantidadCuotasVencidas']++;
                }
            }
        }

        return $resumen;
    }
}
