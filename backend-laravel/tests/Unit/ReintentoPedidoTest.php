<?php

namespace Tests\Unit;

use App\AccesoDatos\PedidoDAO;
use DomainException;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

require_once dirname(__DIR__, 3) . '/autoload.php';

class ReintentoPedidoTest extends TestCase
{
    public function test_rechaza_un_reintento_con_productos_diferentes(): void
    {
        $dao = new PedidoDAO(new PDO('sqlite::memory:'));
        $validar = new ReflectionMethod($dao, 'verificarReintento');
        $pedido = [
            'nombres' => 'Ana', 'apellidos' => 'Prueba', 'dni' => '12345678',
            'telefono' => '900123456', 'correo' => 'ana@example.com', 'modalidad' => 'Contado',
        ];
        $articulos = [[
            'moto_id' => 1,
            'color_id' => null,
            'cantidad' => 1
        ]];
        $datos = $pedido + ['items' => $articulos];
        $validar->invoke($dao, $pedido, $articulos, $datos);

        $datos['items'][0]['cantidad'] = 2;
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Los productos cambiaron');
        $validar->invoke($dao, $pedido, $articulos, $datos);
    }
}
