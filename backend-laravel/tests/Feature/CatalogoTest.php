<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE marcas (id INTEGER PRIMARY KEY, nombre TEXT, estado TEXT)');
        DB::statement('CREATE TABLE motos (id INTEGER PRIMARY KEY, marca_id INTEGER, modelo TEXT, estado TEXT)');
        DB::statement('CREATE TABLE moto_colores (id INTEGER PRIMARY KEY, moto_id INTEGER, nombre TEXT, codigo_hex TEXT, orden INTEGER)');
        DB::statement('CREATE TABLE moto_imagenes (id INTEGER PRIMARY KEY, moto_id INTEGER, ruta TEXT, orden INTEGER)');
        DB::statement('CREATE TABLE moto_color_imagenes (id INTEGER PRIMARY KEY, color_id INTEGER, ruta TEXT, orden INTEGER)');
        DB::statement('CREATE TABLE unidades_moto (id INTEGER PRIMARY KEY, moto_id INTEGER, estado TEXT)');

        DB::table('marcas')->insert(['id' => 1, 'nombre' => 'Bajaj', 'estado' => 'Activo']);
        DB::table('motos')->insert([
            ['id' => 1, 'marca_id' => 1, 'modelo' => 'Pulsar', 'estado' => 'Activo'],
            ['id' => 2, 'marca_id' => 1, 'modelo' => 'Anterior', 'estado' => 'Inactivo'],
        ]);
        DB::table('moto_colores')->insert(['id' => 1, 'moto_id' => 1, 'nombre' => 'Rojo', 'codigo_hex' => '#ff0000', 'orden' => 1]);
        DB::table('moto_color_imagenes')->insert(['id' => 1, 'color_id' => 1, 'ruta' => 'frente.jpg', 'orden' => 1]);
        DB::table('moto_imagenes')->insert(['id' => 1, 'moto_id' => 1, 'ruta' => 'lado.jpg', 'orden' => 1]);
        DB::table('unidades_moto')->insert([
            ['id' => 1, 'moto_id' => 1, 'estado' => 'Disponible'],
            ['id' => 2, 'moto_id' => 1, 'estado' => 'Vendida'],
        ]);
    }

    public function test_muestra_modelos_activos_con_colores_fotos_y_disponibilidad(): void
    {
        $this->getJson('/api/motos')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.marca.nombre', 'Bajaj')
            ->assertJsonPath('data.0.disponibles', 1)
            ->assertJsonPath('data.0.colores.0.imagenes.0.ruta', 'frente.jpg')
            ->assertJsonPath('data.0.imagenes.0.ruta', 'lado.jpg');

        $this->getJson('/api/motos/1')->assertOk()->assertJsonPath('data.modelo', 'Pulsar');
        $this->getJson('/api/motos/2')->assertNotFound();
    }

    public function test_filtra_por_modelo(): void
    {
        $this->getJson('/api/motos?buscar=otra')->assertOk()->assertJsonCount(0, 'data');
    }
}
