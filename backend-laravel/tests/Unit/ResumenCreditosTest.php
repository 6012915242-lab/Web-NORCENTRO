<?php

namespace Tests\Unit;

use App\Servicios\ResumenCreditosServicio;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class ResumenCreditosTest extends TestCase
{
    public function test_calcula_la_cartera_y_las_solicitudes_pendientes(): void
    {
        $creditos = [
            [
                'id' => 1,
                'estado' => 'Activo',
                'saldo' => '1200.50'
            ],
            [
                'id' => 2,
                'estado' => 'Cancelado',
                'saldo' => '0.00'
            ],
        ];
        $cuotas = [1 => [['estado' => 'Vencida'], ['estado' => 'Pendiente']]];
        $solicitudes = [
            ['estado' => 'En revisión de crédito'],
            ['estado' => 'Crédito aprobado'],
        ];

        $resumen = (new ResumenCreditosServicio())->calcular($creditos, $cuotas, $solicitudes);

        $this->assertSame(1, $resumen['creditosPendientes']);
        $this->assertSame(1, $resumen['cantidadCreditosActivos']);
        $this->assertSame(1, $resumen['cantidadCuotasVencidas']);
        $this->assertSame(1200.5, $resumen['saldoCreditoActivo']);
    }
}
