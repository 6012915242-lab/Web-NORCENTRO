<?php

namespace Tests\Unit;

use App\Servicios\ValidacionPedidoServicio;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class ValidacionPedidoTest extends TestCase
{
    private function pedido(): array
    {
        return [
            'request_id' => '12345678-1234-4123-8123-123456789abc',
            'cliente' => [
                'nombres' => 'Ana',
                'apellidos' => 'Prueba',
                'dni' => '12345678',
                'telefono' => '900123456',
                'correo' => 'ana@example.com',
            ],
            'pago' => ['modalidad' => 'Crédito', 'inicial' => '100.00', 'cuotas' => 6],
            'items' => [['moto_id' => 1, 'color_id' => null, 'cantidad' => 1]],
            'acepta_credito' => true,
        ];
    }

    public function test_valida_un_pedido_de_credito(): void
    {
        $resultado = (new ValidacionPedidoServicio())->validar($this->pedido());

        $this->assertSame('Crédito', $resultado['modalidad']);
        $this->assertSame(100.0, $resultado['inicial']);
        $this->assertSame(6, $resultado['cuotas']);
        $this->assertSame(1, $resultado['items'][0]['moto_id']);
    }

    public function test_rechaza_articulos_duplicados(): void
    {
        $pedido = $this->pedido();
        $pedido['items'][] = $pedido['items'][0];

        $this->expectException(InvalidArgumentException::class);
        (new ValidacionPedidoServicio())->validar($pedido);
    }

    public function test_rechaza_una_cantidad_no_entera(): void
    {
        $pedido = $this->pedido();
        $pedido['items'][0]['cantidad'] = '1.5';

        $this->expectException(InvalidArgumentException::class);
        (new ValidacionPedidoServicio())->validar($pedido);
    }

    public function test_rechaza_la_inicial_con_mas_de_dos_decimales(): void
    {
        $pedido = $this->pedido();
        $pedido['pago']['inicial'] = '0.001';

        $this->expectException(InvalidArgumentException::class);
        (new ValidacionPedidoServicio())->validar($pedido);
    }

    public function test_exige_consentimiento_para_credito(): void
    {
        $pedido = $this->pedido();
        $pedido['acepta_credito'] = false;

        $this->expectException(InvalidArgumentException::class);
        (new ValidacionPedidoServicio())->validar($pedido);
    }
}
