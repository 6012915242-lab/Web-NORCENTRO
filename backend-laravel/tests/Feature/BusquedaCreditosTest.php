<?php

namespace Tests\Feature;

use App\AccesoDatos\VentaDAO;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusquedaCreditosTest extends TestCase
{
    public function test_busca_por_dni_y_nombre_completo_sin_mostrar_otros_clientes(): void
    {
        DB::statement('CREATE TABLE clientes (id INTEGER PRIMARY KEY, dni TEXT, nombres TEXT, apellidos TEXT)');
        DB::statement('CREATE TABLE marcas (id INTEGER PRIMARY KEY, nombre TEXT)');
        DB::statement('CREATE TABLE motos (id INTEGER PRIMARY KEY, marca_id INTEGER, modelo TEXT)');
        DB::statement('CREATE TABLE unidades_moto (id INTEGER PRIMARY KEY, moto_id INTEGER)');
        DB::statement('CREATE TABLE ventas (id INTEGER PRIMARY KEY, cliente_id INTEGER, unidad_id INTEGER)');
        DB::statement('CREATE TABLE creditos (id INTEGER PRIMARY KEY, venta_id INTEGER)');

        DB::table('clientes')->insert([
            [
                'id' => 1,
                'dni' => '12345678',
                'nombres' => 'Ana María',
                'apellidos' => 'Pérez López'
            ],
            [
                'id' => 2,
                'dni' => '87654321',
                'nombres' => 'Luis',
                'apellidos' => 'Rojas'
            ],
        ]);
        DB::table('marcas')->insert(['id' => 1, 'nombre' => 'Bajaj']);
        DB::table('motos')->insert(['id' => 1, 'marca_id' => 1, 'modelo' => 'Pulsar']);
        DB::table('unidades_moto')->insert(['id' => 1, 'moto_id' => 1]);
        DB::table('ventas')->insert([
            ['id' => 1, 'cliente_id' => 1, 'unidad_id' => 1],
            ['id' => 2, 'cliente_id' => 2, 'unidad_id' => 1],
        ]);
        DB::table('creditos')->insert([
            ['id' => 1, 'venta_id' => 1],
            ['id' => 2, 'venta_id' => 2],
        ]);

        $dao = new VentaDAO(DB::connection()->getPdo());

        self::assertCount(2, $dao->creditos());
        self::assertSame('12345678', $dao->creditos('12345678')[0]['dni']);
        self::assertSame('12345678', $dao->creditos('Ana Pérez')[0]['dni']);
        self::assertCount(0, $dao->creditos("' OR 1=1 --"));
    }
}
