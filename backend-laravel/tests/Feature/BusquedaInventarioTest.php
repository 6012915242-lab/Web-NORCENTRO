<?php

namespace Tests\Feature;

use App\AccesoDatos\MotoDAO;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusquedaInventarioTest extends TestCase
{
    public function test_filtra_por_serie_modelo_y_estado(): void
    {
        DB::statement('CREATE TABLE marcas (id INTEGER PRIMARY KEY, nombre TEXT)');
        DB::statement('CREATE TABLE motos (id INTEGER PRIMARY KEY, marca_id INTEGER, modelo TEXT, imagen TEXT)');
        DB::statement('CREATE TABLE unidades_moto (id INTEGER PRIMARY KEY, moto_id INTEGER, codigo_serie TEXT, estado TEXT)');
        DB::table('marcas')->insert(['id' => 1, 'nombre' => 'Bajaj']);
        DB::table('motos')->insert([
            'id' => 1,
            'marca_id' => 1,
            'modelo' => 'Pulsar',
            'imagen' => ''
        ]);
        DB::table('unidades_moto')->insert([
            [
                'id' => 1,
                'moto_id' => 1,
                'codigo_serie' => 'NC-001',
                'estado' => 'Disponible'
            ],
            [
                'id' => 2,
                'moto_id' => 1,
                'codigo_serie' => 'NC-002',
                'estado' => 'Vendida'
            ],
        ]);

        $dao = new MotoDAO(DB::connection()->getPdo());

        self::assertCount(2, $dao->inventario('Pulsar'));
        self::assertSame('NC-001', $dao->inventario('Bajaj', 'Disponible')[0]['codigo_serie']);
        self::assertSame('NC-002', $dao->inventario('NC-002')[0]['codigo_serie']);
        self::assertCount(0, $dao->inventario("' OR 1=1 --"));
    }
}
