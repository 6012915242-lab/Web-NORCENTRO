<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsultaDniTest extends TestCase
{
    public function test_requiere_sesion(): void
    {
        $this->postJson('/api/consulta-dni', ['dni' => '12345678'])->assertUnauthorized();
    }

    public function test_requiere_permiso_de_clientes(): void
    {
        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));

        $this->postJson('/api/consulta-dni', ['dni' => '12345678'])->assertForbidden();
    }

    public function test_rechaza_dni_invalido_antes_de_contactar_el_servicio(): void
    {
        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::table('vista_usuarios_permisos')->insert([
            'usuario_id' => 1,
            'permiso' => 'clientes.gestionar',
        ]);
        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));

        $this->postJson('/api/consulta-dni', ['dni' => '123'])->assertUnprocessable();
    }
}
