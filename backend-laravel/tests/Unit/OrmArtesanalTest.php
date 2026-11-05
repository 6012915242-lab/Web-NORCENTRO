<?php

namespace Tests\Unit;

use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\VentaDAO;
use App\Modelos\Cliente;
use App\Modelos\Moto;
use PDO;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/autoload.php';

class OrmArtesanalTest extends TestCase
{
    public function test_mapea_un_cliente_a_su_entidad(): void
    {
        $conexion = $this->conexionTemporal();
        $conexion->exec('CREATE TABLE clientes (id INTEGER, dni TEXT, nombres TEXT, apellidos TEXT, telefono TEXT, direccion TEXT, correo TEXT, estado TEXT)');
        $conexion->exec("INSERT INTO clientes VALUES (1, '12345678', 'Ana', 'Prueba', '', '', '', 'Activo')");

        $cliente = (new ClienteDAO($conexion))->obtenerEntidad(1);

        $this->assertInstanceOf(Cliente::class, $cliente);
        $this->assertSame('Ana Prueba', $cliente->nombreCompleto());
        $this->assertNull((new ClienteDAO($conexion))->obtenerEntidad(2));
    }

    public function test_mapea_una_moto_con_su_marca_relacionada(): void
    {
        $conexion = $this->conexionTemporal();
        $conexion->exec('CREATE TABLE marcas (id INTEGER, nombre TEXT)');
        $conexion->exec('CREATE TABLE motos (id INTEGER, marca_id INTEGER, modelo TEXT, cilindrada INTEGER, precio REAL, estado TEXT)');
        $conexion->exec("INSERT INTO marcas VALUES (1, 'Bajaj')");
        $conexion->exec("INSERT INTO motos VALUES (2, 1, 'Pulsar', 160, 9000, 'Activo')");

        $moto = (new MotoDAO($conexion))->obtenerEntidad(2);

        $this->assertInstanceOf(Moto::class, $moto);
        $this->assertSame('Bajaj', $moto->marca);
        $this->assertSame(160, $moto->cilindrada);
    }

    public function test_agrupa_fotografias_por_moto_y_color(): void
    {
        $conexion = $this->conexionTemporal();
        $conexion->exec('CREATE TABLE moto_colores (id INTEGER, moto_id INTEGER, nombre TEXT, codigo_hex TEXT, orden INTEGER)');
        $conexion->exec('CREATE TABLE moto_color_imagenes (id INTEGER, color_id INTEGER, ruta TEXT, texto_alt TEXT, orden INTEGER)');
        $conexion->exec("INSERT INTO moto_colores VALUES (1, 10, 'Rojo', '#ff0000', 1), (2, 20, 'Azul', '#0000ff', 1)");
        $conexion->exec("INSERT INTO moto_color_imagenes VALUES (1, 1, 'frente.jpg', 'Frente', 1), (2, 1, 'lado.jpg', 'Lado', 2), (3, 2, 'azul.jpg', 'Azul', 1)");

        $dao = new MotoDAO($conexion);
        $colores = $dao->coloresPorMotos([10, 20, 10, 30]);

        $this->assertSame('Rojo', $colores[10][0]['nombre']);
        $this->assertCount(2, $colores[10][0]['imagenes']);
        $this->assertSame('lado.jpg', $colores[10][0]['imagenes'][1]['ruta']);
        $this->assertSame('Azul', $colores[20][0]['nombre']);
        $this->assertSame($colores[10], $dao->coloresPublicos(10));
        $this->assertSame([], $dao->coloresPublicos(30));
        $this->assertSame([], $dao->coloresPorMotos([]));
    }

    public function test_agrupa_cuotas_y_pagos_de_varios_creditos(): void
    {
        $conexion = $this->conexionTemporal();
        $conexion->sqliteCreateFunction('CURDATE', static fn (): string => '2026-01-01');
        $conexion->exec('CREATE TABLE cuotas (id INTEGER, credito_id INTEGER, numero INTEGER, fecha_vencimiento TEXT, estado TEXT)');
        $conexion->exec('CREATE TABLE pagos (id INTEGER, credito_id INTEGER, cuota_id INTEGER, monto REAL, fecha TEXT, concepto TEXT)');
        $conexion->exec("INSERT INTO cuotas VALUES (1, 10, 1, '2025-12-01', 'Pendiente'), (2, 20, 1, '2026-02-01', 'Pendiente')");
        $conexion->exec("INSERT INTO pagos VALUES (1, 10, 1, 100, '2025-12-03', 'Abono')");

        $dao = new VentaDAO($conexion);
        $resultado = $dao->detallesCreditos([10, 20, 30]);

        $this->assertSame('Vencida', $resultado['cuotas'][10][0]['estado']);
        $this->assertSame('Pendiente', $resultado['cuotas'][20][0]['estado']);
        $this->assertSame(1, $resultado['pagos'][10][0]['cuota_numero']);
        $this->assertSame([], $resultado['cuotas'][30]);
        $this->assertSame([], $resultado['pagos'][30]);
        $this->assertSame(['cuotas' => [], 'pagos' => []], $dao->detallesCreditos([]));
    }

    private function conexionTemporal(): PDO
    {
        return new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
