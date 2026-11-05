<?php

namespace Tests\Unit;

use App\AccesoDatos\PedidoDAO;
use App\Servicios\EvaluacionCreditoServicio;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class EvaluacionCreditoServicioTest extends TestCase
{
    public function test_rechaza_identificador_de_solicitud_en_formato_de_arreglo(): void
    {
        $conexion = new PDO('sqlite::memory:');
        $servicio = new EvaluacionCreditoServicio();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No se encontró la solicitud de crédito.');

        $servicio->evaluarSolicitudCredito(new PedidoDAO($conexion), [
            'pedido_id' => [1],
            'decision' => 'aprobar',
        ]);
    }
}
