<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SesionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::connection()->getPdo()->sqliteCreateFunction('NOW', static fn () => '2026-01-01 00:00:00');
        DB::statement('CREATE TABLE roles (id INTEGER PRIMARY KEY, nombre TEXT)');
        DB::statement('CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY, rol_id INTEGER, usuario TEXT, password_hash TEXT, nombre TEXT,
            estado TEXT, intentos_fallidos INTEGER, bloqueado_hasta TEXT, ultimo_acceso TEXT
        )');
        DB::statement('CREATE TABLE intentos_acceso (usuario TEXT, direccion_ip TEXT, exitoso INTEGER, motivo TEXT)');
        DB::table('roles')->insert(['id' => 1, 'nombre' => 'Administrador']);
        DB::table('usuarios')->insert([
            'id' => 1, 'rol_id' => 1, 'usuario' => 'prueba',
            'password_hash' => password_hash('clave-prueba', PASSWORD_DEFAULT),
            'nombre' => 'Usuario de prueba', 'estado' => 'Activo', 'intentos_fallidos' => 0,
        ]);
    }

    public function test_inicia_y_cierra_la_sesion(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk()
            ->assertJsonPath('nombre', 'Usuario de prueba');

        $this->assertAuthenticated();
        $this->postJson('/sesion/cerrar')->assertOk();
        $this->assertGuest();
    }

    public function test_rechaza_un_usuario_desconocido(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'desconocido', 'password' => 'clave'])
            ->assertUnprocessable();
        $this->assertGuest();
    }
}
