<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VentasCreditoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('CREATE TABLE vista_usuarios_permisos (usuario_id INTEGER, permiso TEXT)');
        DB::statement('CREATE TABLE creditos (id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE cuotas (id INTEGER, credito_id INTEGER, numero INTEGER, monto REAL)');
        DB::table('vista_usuarios_permisos')->insert([
            ['usuario_id' => 1, 'permiso' => 'ventas.registrar'],
            ['usuario_id' => 1, 'permiso' => 'creditos.revisar'],
        ]);
        $this->actingAs((new Usuario())->forceFill(['id' => 1, 'estado' => 'Activo']));
    }

    public function test_rechaza_datos_invalidos_en_venta_y_pago(): void
    {
        $this->postJson('/api/ventas', [])->assertUnprocessable();
        $this->postJson('/api/creditos/pagos', ['cuota_id' => 1, 'monto' => 0])
            ->assertUnprocessable();
        $this->postJson('/api/creditos/pagos', ['cuota_id' => 1, 'monto' => '0.001'])
            ->assertUnprocessable();
        $this->postJson('/api/solicitudes-credito/1/evaluacion', ['decision' => 'desconocida'])
            ->assertUnprocessable();
    }

    public function test_rechaza_descuentos_e_iniciales_con_mas_de_dos_decimales(): void
    {
        $venta = [
            'cliente_id' => 1,
            'unidad_id' => 1,
            'modalidad' => 'Crédito',
            'descuento' => '0.001',
            'inicial' => '100.00',
            'numero_cuotas' => 3,
            'fecha_primera_cuota' => '2099-01-31',
        ];

        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
        $venta['descuento'] = '0';
        $venta['inicial'] = '100.001';
        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
    }

    public function test_consulta_cuotas_del_credito_solicitado(): void
    {
        DB::table('creditos')->insert(['id' => 5]);
        DB::table('cuotas')->insert([
            ['id' => 1, 'credito_id' => 5, 'numero' => 1, 'monto' => 1200],
            ['id' => 2, 'credito_id' => 6, 'numero' => 1, 'monto' => 500],
        ]);

        $this->getJson('/api/creditos/5/cuotas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 1);
    }

    public function test_un_credito_inexistente_no_muestra_un_cronograma_vacio(): void
    {
        $this->getJson('/api/creditos/999/cuotas')->assertNotFound();
    }

    public function test_separacion_de_permisos_de_ventas_y_creditos(): void
    {
        DB::table('vista_usuarios_permisos')->where('permiso', 'creditos.revisar')->delete();

        $this->getJson('/api/creditos/5/cuotas')->assertForbidden();
        $this->postJson('/api/creditos/pagos', [])->assertForbidden();
        $this->postJson('/api/solicitudes-credito/1/evaluacion', ['decision' => 'aprobar'])
            ->assertForbidden();
        $this->postJson('/api/ventas', [])->assertUnprocessable();
    }

    public function test_exige_sesion(): void
    {
        auth()->logout();

        $this->postJson('/api/ventas', [])->assertUnauthorized();
        $this->getJson('/api/creditos')->assertUnauthorized();
        $this->getJson('/api/solicitudes-credito')->assertUnauthorized();
    }
}
