<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::statement('CREATE TABLE marcas (id INTEGER PRIMARY KEY, nombre TEXT)');
        DB::statement('CREATE TABLE motos (
            id INTEGER PRIMARY KEY, marca_id INTEGER, modelo TEXT, imagen TEXT, estado TEXT
        )');
        DB::statement('CREATE TABLE unidades_moto (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            moto_id INTEGER,
            codigo_serie TEXT UNIQUE,
            estado TEXT
        )');
        DB::table('marcas')->insert(['id' => 1, 'nombre' => 'Bajaj']);
        DB::table('motos')->insert([
            ['id' => 1, 'marca_id' => 1, 'modelo' => 'Pulsar', 'imagen' => '', 'estado' => 'Activo'],
            ['id' => 2, 'marca_id' => 1, 'modelo' => 'Otro', 'imagen' => '', 'estado' => 'Inactivo'],
        ]);
        DB::table('vista_usuarios_permisos')->insert([
            'usuario_id' => 1,
            'permiso' => 'inventario.gestionar',
        ]);

        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));
    }

    public function test_registra_una_unidad_y_normaliza_su_codigo(): void
    {
        $this->postJson('/api/inventario', [
            'moto_id' => 1,
            'codigo_serie' => '  moto-123  ',
        ])->assertCreated()
            ->assertJsonPath('data.codigo_serie', 'MOTO-123')
            ->assertJsonPath('data.estado', 'Disponible');
    }

    public function test_rechaza_series_duplicadas_y_motos_no_activas(): void
    {
        $datos = ['moto_id' => 1, 'codigo_serie' => 'MOTO-123'];
        $this->postJson('/api/inventario', $datos)->assertCreated();
        $this->postJson('/api/inventario', $datos)->assertUnprocessable();
        $this->postJson('/api/inventario', [
            'moto_id' => 2,
            'codigo_serie' => 'MOTO-456',
        ])->assertUnprocessable();
        $this->postJson('/api/inventario', [
            'moto_id' => 1,
            'codigo_serie' => '!',
        ])->assertUnprocessable();
    }

    public function test_exige_permiso_para_registrar_unidades(): void
    {
        DB::table('vista_usuarios_permisos')->delete();

        $this->postJson('/api/inventario', [
            'moto_id' => 1,
            'codigo_serie' => 'MOTO-123',
        ])->assertForbidden();
    }

    public function test_muestra_las_unidades_registradas(): void
    {
        DB::table('unidades_moto')->insert([
            'moto_id' => 1,
            'codigo_serie' => 'MOTO-123',
            'estado' => 'Disponible',
        ]);

        $this->getJson('/api/inventario')
            ->assertOk()
            ->assertJsonPath('data.0.codigo_serie', 'MOTO-123')
            ->assertJsonPath('data.0.marca', 'Bajaj');
    }
}
