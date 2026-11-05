<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccesoClientesTest extends TestCase
{
    public function test_una_persona_sin_sesion_no_puede_consultar_clientes(): void
    {
        $this->getJson('/api/clientes')->assertUnauthorized();
        $this->getJson('/api/clientes/1')->assertUnauthorized();
        $this->get('/api/clientes')->assertUnauthorized();
    }

    public function test_un_usuario_sin_permiso_no_puede_consultar_clientes(): void
    {
        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        $usuario = (new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']);

        $this->actingAs($usuario)
            ->getJson('/api/clientes')
            ->assertForbidden();
    }

    public function test_un_usuario_inactivo_no_puede_usar_sus_permisos(): void
    {
        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::table('vista_usuarios_permisos')->insert(['usuario_id' => 1, 'permiso' => 'clientes.gestionar']);
        $usuario = (new Usuario())->forceFill(['id' => 1, 'estado' => 'Inactivo']);

        $this->actingAs($usuario)
            ->getJson('/api/clientes')
            ->assertForbidden();
    }

    public function test_un_usuario_con_permiso_puede_consultar_clientes(): void
    {
        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::statement('CREATE TABLE clientes (id INTEGER, dni TEXT, nombres TEXT, apellidos TEXT, telefono TEXT, correo TEXT, estado TEXT)');
        DB::table('vista_usuarios_permisos')->insert(['usuario_id' => 1, 'permiso' => 'clientes.gestionar']);
        DB::table('clientes')->insert([
            'id' => 1, 'dni' => '12345678', 'nombres' => 'Ana', 'apellidos' => 'Prueba',
            'telefono' => '', 'correo' => '', 'estado' => 'Activo',
        ]);
        $usuario = (new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']);

        $this->actingAs($usuario)
            ->getJson('/api/clientes')
            ->assertOk()
            ->assertJsonPath('data.0.nombres', 'Ana');
    }
}
