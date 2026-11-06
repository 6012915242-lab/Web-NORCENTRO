<?php

namespace Tests\Unit;

use App\Servicios\InicioServicio;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class InicioServicioTest extends TestCase
{
    public function test_selecciona_modelo_y_oferta_con_mayor_descuento(): void
    {
        $motos = [
            ['id' => 1, 'modelo' => 'YZF-R15 ABS'],
            ['id' => 2, 'modelo' => 'Duke 200'],
        ];
        $ofertas = [
            [
                'id' => 1,
                'precio_normal' => 1000,
                'precio_oferta' => 900
            ],
            [
                'id' => 2,
                'precio_normal' => 1000,
                'precio_oferta' => 800
            ],
            [
                'id' => 3,
                'precio_normal' => 0,
                'precio_oferta' => 0
            ],
        ];

        $resultado = (new InicioServicio())->seleccionarDestacados($motos, $motos[0], $ofertas);

        $this->assertSame(2, $resultado['motosDestacadas'][0]['id']);
        $this->assertSame(2, $resultado['ofertaDestacada']['id']);
    }

    public function test_no_destaca_una_oferta_sin_descuento(): void
    {
        $resultado = (new InicioServicio())->seleccionarDestacados([], null, [
            ['precio_normal' => 100, 'precio_oferta' => 100],
        ]);

        $this->assertSame([], $resultado['motosDestacadas']);
        $this->assertNull($resultado['ofertaDestacada']);
    }
}
