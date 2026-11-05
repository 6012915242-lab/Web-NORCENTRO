<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GestionClientesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::statement('CREATE TABLE clientes (
            id INTEGER PRIMARY KEY AUTOINCREMENT, dni TEXT UNIQUE, nombres TEXT, apellidos TEXT,
            telefono TEXT, direccion TEXT, correo TEXT, estado TEXT
        )');
        DB::table('vista_usuarios_permisos')->insert([
            ['usuario_id' => 1, 'permiso' => 'clientes.gestionar'],
            ['usuario_id' => 1, 'permiso' => 'clientes.desactivar'],
        ]);

        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));
    }

    public function test_registra_actualiza_y_desactiva_un_cliente(): void
    {
        $datos = ['dni' => '12345678', 'nombres' => 'Ana', 'apellidos' => 'Prueba'];
        $this->postJson('/api/clientes', $datos)->assertCreated()->assertJsonPath('data.estado', 'Activo');
        $datos['nombres'] = 'Andrea';
        $this->putJson('/api/clientes/1', $datos)->assertOk()->assertJsonPath('data.nombres', 'Andrea');
        $this->deleteJson('/api/clientes/1')->assertOk();
        $this->getJson('/api/clientes/1')->assertNotFound();
    }

    public function test_rechaza_dni_invalido_y_duplicado(): void
    {
        $datos = ['dni' => '12', 'nombres' => 'Ana', 'apellidos' => 'Prueba'];
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();
        $datos['dni'] = '12345678';
        $this->postJson('/api/clientes', $datos)->assertCreated();
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();
    }

    public function test_exige_permiso_especifico_para_desactivar(): void
    {
        DB::table('vista_usuarios_permisos')->where('permiso', 'clientes.desactivar')->delete();
        $this->deleteJson('/api/clientes/1')->assertForbidden();
    }
}
