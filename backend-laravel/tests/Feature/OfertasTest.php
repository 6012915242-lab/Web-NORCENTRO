<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfertasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::statement('CREATE TABLE marcas (id INTEGER PRIMARY KEY, nombre TEXT, estado TEXT)');
        DB::statement('CREATE TABLE motos (id INTEGER PRIMARY KEY, marca_id INTEGER, modelo TEXT, estado TEXT)');
        DB::statement('CREATE TABLE ofertas (
            id INTEGER PRIMARY KEY AUTOINCREMENT, moto_id INTEGER, titulo TEXT, descripcion TEXT,
            precio_normal REAL, precio_oferta REAL, fecha_inicio TEXT, fecha_fin TEXT, estado TEXT
        )');
        DB::table('marcas')->insert(['id' => 1, 'nombre' => 'Bajaj', 'estado' => 'Activo']);
        DB::table('motos')->insert(['id' => 1, 'marca_id' => 1, 'modelo' => 'Pulsar', 'estado' => 'Activo']);
        DB::table('vista_usuarios_permisos')->insert(['usuario_id' => 1, 'permiso' => 'ofertas.gestionar']);

        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));
    }

    private function datosOferta(): array
    {
        return [
            'moto_id' => 1,
            'titulo' => 'Oferta especial',
            'descripcion' => 'Precio promocional',
            'precio_normal' => 10000,
            'precio_oferta' => 9000,
            'fecha_inicio' => '2026-11-01',
            'fecha_fin' => '2026-11-30',
        ];
    }

    public function test_registra_modifica_lista_y_desactiva_una_oferta(): void
    {
        $datos = $this->datosOferta();
        $this->postJson('/api/ofertas', $datos)->assertCreated()->assertJsonPath('data.estado', 'Activa');
        $datos['titulo'] = 'Promoción actualizada';
        $this->putJson('/api/ofertas/1', $datos)->assertOk()->assertJsonPath('data.titulo', $datos['titulo']);
        $this->getJson('/api/ofertas')->assertOk()->assertJsonPath('data.0.moto.marca.nombre', 'Bajaj');
        $this->deleteJson('/api/ofertas/1')->assertOk();
        $this->assertDatabaseHas('ofertas', ['id' => 1, 'estado' => 'Inactiva']);
    }

    public function test_rechaza_precios_fechas_y_modelos_invalidos(): void
    {
        $datos = $this->datosOferta();
        $datos['precio_oferta'] = $datos['precio_normal'];
        $this->postJson('/api/ofertas', $datos)->assertUnprocessable();
        $datos = $this->datosOferta();
        $datos['fecha_fin'] = '2026-10-01';
        $this->postJson('/api/ofertas', $datos)->assertUnprocessable();
        $datos = $this->datosOferta();
        $datos['moto_id'] = 999;
        $this->postJson('/api/ofertas', $datos)->assertUnprocessable();
        $this->assertDatabaseCount('ofertas', 0);
    }

    public function test_requiere_permiso_para_gestionar_ofertas(): void
    {
        DB::table('vista_usuarios_permisos')->delete();
        $this->getJson('/api/ofertas')->assertForbidden();
        $this->postJson('/api/ofertas', $this->datosOferta())->assertForbidden();
    }
}
