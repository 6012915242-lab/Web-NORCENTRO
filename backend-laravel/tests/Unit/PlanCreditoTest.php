<?php

namespace Tests\Unit;

use App\Modelos\PlanCredito;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class PlanCreditoTest extends TestCase
{
    public function test_aplica_el_recargo_y_distribuye_los_centavos(): void
    {
        $plan = PlanCredito::calcular(10000, 2000, 3);

        $this->assertEquals(400.0, $plan['recargo']);
        $this->assertEquals(8400.0, $plan['financiado']);
        $this->assertEquals($plan['financiado'], array_sum($plan['cuotas']));
        $this->assertCount(3, $plan['cuotas']);
    }

    public function test_conserva_el_dia_de_pago_sin_saltar_meses(): void
    {
        $fechas = PlanCredito::fechasCuotas(new DateTimeImmutable('2027-01-31'), 3);

        $this->assertSame(
            ['2027-01-31', '2027-02-28', '2027-03-31'],
            array_map(static fn (DateTimeImmutable $fecha): string => $fecha->format('Y-m-d'), $fechas)
        );
    }

    public function test_no_acepta_una_inicial_igual_al_precio(): void
    {
        $this->expectException(DomainException::class);
        PlanCredito::calcular(1000, 1000, 3);
    }
}
