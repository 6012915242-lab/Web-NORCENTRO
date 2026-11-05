<?php

namespace Tests\Unit;

use App\AccesoDatos\MotoDAO;
use App\Servicios\GestionMotosServicio;
use DomainException;
use PDO;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class ValidacionMotoTest extends TestCase
{
    private function datosValidos(): array
    {
        return [
            'marca_id' => 1,
            'modelo' => 'Pulsar',
            'cilindrada' => 160,
            'anio' => (int) date('Y'),
            'precio' => '9000.00',
            'cantidad' => 1,
        ];
    }

    public function test_rechaza_un_modelo_que_no_cabe_en_la_base(): void
    {
        $datos = $this->datosValidos();
        $datos['modelo'] = str_repeat('A', 101);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('2 a 100 caracteres');
        (new GestionMotosServicio())->validarYGuardarMoto(
            new MotoDAO(new PDO('sqlite::memory:')),
            $datos,
            []
        );
    }

    public function test_rechaza_un_precio_con_mas_de_dos_decimales(): void
    {
        $datos = $this->datosValidos();
        $datos['precio'] = '9000.001';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cilindrada');
        (new GestionMotosServicio())->validarYGuardarMoto(
            new MotoDAO(new PDO('sqlite::memory:')),
            $datos,
            []
        );
    }

    public function test_rechaza_un_color_que_no_cabe_en_el_modelo(): void
    {
        $conexion = new PDO('sqlite::memory:');
        $conexion->exec('CREATE TABLE marcas (id INTEGER, estado TEXT)');
        $conexion->exec("INSERT INTO marcas VALUES (1, 'Activo')");
        $datos = $this->datosValidos();
        $datos['colores'] = [['nombre' => str_repeat('A', 51), 'codigo_hex' => '#ff0000']];

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('2 a 50 caracteres');
        (new GestionMotosServicio())->validarYGuardarMoto(new MotoDAO($conexion), $datos, []);
    }

    public function test_rechaza_una_estructura_de_modelo_invalida(): void
    {
        $datos = $this->datosValidos();
        $datos['modelo'] = ['Pulsar'];

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('texto válido');
        (new GestionMotosServicio())->validarYGuardarMoto(
            new MotoDAO(new PDO('sqlite::memory:')),
            $datos,
            []
        );
    }
}
