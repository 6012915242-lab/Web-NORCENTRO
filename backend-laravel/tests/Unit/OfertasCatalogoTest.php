<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Servicios\OfertasCatalogoServicio;
use PHPUnit\Framework\TestCase;

final class OfertasCatalogoTest extends TestCase
{
    public function test_conserva_la_primera_oferta_de_cada_moto(): void
    {
        $ofertas = [
            ['moto_id' => 2, 'precio_oferta' => 9000],
            ['moto_id' => 2, 'precio_oferta' => 9500],
            ['moto_id' => 3, 'precio_oferta' => 12000],
        ];

        $resultado = (new OfertasCatalogoServicio)->agruparPorMoto($ofertas);

        self::assertCount(2, $resultado);
        self::assertSame(9000, $resultado[2]['precio_oferta']);
        self::assertSame(12000, $resultado[3]['precio_oferta']);
    }
}
