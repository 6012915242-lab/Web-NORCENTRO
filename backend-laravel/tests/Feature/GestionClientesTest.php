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
            telefono TEXT NOT NULL, direccion TEXT NOT NULL, correo TEXT NOT NULL,
            estado TEXT NOT NULL DEFAULT "Activo"
        )');
        DB::table('vista_usuarios_permisos')->insert([
            [
                'usuario_id' => 1,
                'permiso' => 'clientes.gestionar'
            ],
            [
                'usuario_id' => 1,
                'permiso' => 'clientes.desactivar'
            ],
        ]);

        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));
    }

    public function test_registra_actualiza_y_desactiva_un_cliente(): void
    {
        $datos = [
            'dni' => '12345678',
            'nombres' => 'Ana',
            'apellidos' => 'Prueba'
        ];
        $this->postJson('/api/clientes', $datos)->assertCreated()->assertJsonPath('data.estado', 'Activo');
        $datos['nombres'] = 'Andrea';
        $this->putJson('/api/clientes/1', $datos)->assertOk()->assertJsonPath('data.nombres', 'Andrea');
        $this->deleteJson('/api/clientes/1')->assertOk();
        $this->getJson('/api/clientes/1')->assertNotFound();
    }

    public function test_rechaza_dni_invalido_y_duplicado(): void
    {
        $datos = [
            'dni' => '12',
            'nombres' => 'Ana',
            'apellidos' => 'Prueba'
        ];
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();
        $datos['dni'] = '12345678';
        $this->postJson('/api/clientes', $datos)->assertCreated();
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();
    }

    public function test_guarda_los_campos_opcionales_vacios(): void
    {
        $this->postJson('/api/clientes', [
            'dni' => '87654321',
            'nombres' => 'Ana',
            'apellidos' => 'Prueba',
            'telefono' => null,
            'correo' => null,
            'direccion' => null,
        ])->assertCreated()->assertJsonPath('data.direccion', '');

        $this->assertDatabaseHas('clientes', [
            'dni' => '87654321',
            'telefono' => '',
            'correo' => '',
            'direccion' => '',
        ]);
    }

    public function test_valida_los_nombres_y_acepta_acentos(): void
    {
        $datos = [
            'dni' => '87654321',
            'nombres' => '<script>',
            'apellidos' => 'Prueba'
        ];
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();

        $datos['nombres'] = 'José María';
        $datos['apellidos'] = 'Muñoz';
        $this->postJson('/api/clientes', $datos)->assertCreated();
    }

    public function test_rechaza_campos_del_cliente_con_estructura_invalida(): void
    {
        $this->postJson('/api/clientes', [
            'dni' => '87654321',
            'nombres' => ['Ana'],
            'apellidos' => 'Prueba',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_puede_buscar_el_digito_cero(): void
    {
        $this->postJson('/api/clientes', [
            'dni' => '12345678', 'nombres' => 'Ana', 'apellidos' => 'Prueba',
        ])->assertCreated();
        $this->postJson('/api/clientes', [
            'dni' => '01234567', 'nombres' => 'José', 'apellidos' => 'Muñoz',
        ])->assertCreated();

        $this->getJson('/api/clientes?buscar=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.dni', '01234567');
    }

    public function test_exige_permiso_especifico_para_desactivar(): void
    {
        DB::table('vista_usuarios_permisos')->where('permiso', 'clientes.desactivar')->delete();
        $this->deleteJson('/api/clientes/1')->assertForbidden();
    }
}
