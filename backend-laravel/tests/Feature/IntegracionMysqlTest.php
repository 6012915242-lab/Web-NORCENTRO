<?php

namespace Tests\Feature;

use App\Configuracion\BaseDatos;
use App\AccesoDatos\VentaDAO;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Prueba opcional con tablas temporales, sin modificar los datos de Norcentro. */
class IntegracionMysqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('NORCENTRO_PRUEBA_MYSQL') !== '1') {
            $this->markTestSkipped('Activa NORCENTRO_PRUEBA_MYSQL para ejecutar la prueba con MySQL.');
        }

        $conexion = BaseDatos::conectar();
        DB::setDefaultConnection('mysql');
        DB::connection('mysql')->setPdo($conexion);

        $tablas = [
            'CREATE TEMPORARY TABLE vista_usuarios_permisos (usuario_id INT, permiso VARCHAR(100))',
            'CREATE TEMPORARY TABLE roles (id INT PRIMARY KEY, nombre VARCHAR(50))',
            'CREATE TEMPORARY TABLE usuarios (id INT PRIMARY KEY, rol_id INT, usuario VARCHAR(50), password_hash VARCHAR(255), nombre VARCHAR(100), estado VARCHAR(20), intentos_fallidos INT DEFAULT 0, bloqueado_hasta DATETIME NULL, ultimo_acceso DATETIME NULL)',
            'CREATE TEMPORARY TABLE intentos_acceso (usuario VARCHAR(50), direccion_ip VARCHAR(45), exitoso INT, motivo VARCHAR(120))',
            'CREATE TEMPORARY TABLE clientes (id INT AUTO_INCREMENT PRIMARY KEY, dni CHAR(8) UNIQUE, nombres VARCHAR(100), apellidos VARCHAR(100), telefono VARCHAR(20) NOT NULL DEFAULT "", direccion VARCHAR(180) NOT NULL DEFAULT "", correo VARCHAR(120) NOT NULL DEFAULT "", estado VARCHAR(20) NOT NULL DEFAULT "Activo")',
            'CREATE TEMPORARY TABLE marcas (id INT PRIMARY KEY, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE motos (id INT PRIMARY KEY, marca_id INT, precio DECIMAL(10,2), estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE unidades_moto (id INT PRIMARY KEY, moto_id INT, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE ventas (id INT AUTO_INCREMENT PRIMARY KEY, cliente_id INT, usuario_id INT, unidad_id INT, precio DECIMAL(10,2), descuento DECIMAL(10,2), total DECIMAL(10,2), modalidad VARCHAR(20))',
            'CREATE TEMPORARY TABLE creditos (id INT AUTO_INCREMENT PRIMARY KEY, venta_id INT, inicial DECIMAL(10,2), monto_financiado DECIMAL(10,2), numero_cuotas INT, saldo DECIMAL(10,2), estado VARCHAR(20), porcentaje_recargo DECIMAL(5,2), importe_recargo DECIMAL(10,2))',
            'CREATE TEMPORARY TABLE cuotas (id INT AUTO_INCREMENT PRIMARY KEY, credito_id INT, numero INT, fecha_vencimiento DATE, monto DECIMAL(10,2), pagado DECIMAL(10,2) DEFAULT 0, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE pagos (id INT AUTO_INCREMENT PRIMARY KEY, credito_id INT, cuota_id INT NULL, monto DECIMAL(10,2), fecha DATE, concepto VARCHAR(40))',
        ];

        foreach ($tablas as $tabla) {
            $conexion->exec($tabla);
        }

        $conexion->exec("INSERT INTO vista_usuarios_permisos VALUES
            (1, 'ventas.registrar'), (1, 'creditos.revisar'),
            (1, 'clientes.gestionar'), (1, 'clientes.desactivar')");
        $conexion->exec("INSERT INTO roles VALUES (1, 'Administrador')");
        $usuario = $conexion->prepare('INSERT INTO usuarios (id, rol_id, usuario, password_hash, nombre, estado) VALUES (1, 1, :usuario, :clave, :nombre, :estado)');
        $usuario->execute([
            'usuario' => 'prueba',
            'clave' => password_hash('clave-prueba', PASSWORD_DEFAULT),
            'nombre' => 'Usuario de prueba',
            'estado' => 'Activo',
        ]);
        $conexion->exec("INSERT INTO clientes (id, dni, nombres, apellidos, estado)
            VALUES (1, '11111111', 'Cliente', 'Prueba', 'Activo')");
        $conexion->exec("INSERT INTO marcas VALUES (1, 'Activo')");
        $conexion->exec("INSERT INTO motos VALUES (1, 1, 10000, 'Activo')");
        $conexion->exec("INSERT INTO unidades_moto VALUES (1, 1, 'Disponible')");

    }

    public function test_venta_credito_y_abono_desde_las_rutas_de_laravel(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();

        $venta = [
            'cliente_id' => 1,
            'unidad_id' => 1,
            'modalidad' => 'Crédito',
            'descuento' => 0,
            'inicial' => 2000,
            'numero_cuotas' => 3,
            'fecha_primera_cuota' => '2099-01-31',
        ];

        $this->postJson('/api/ventas', $venta)
            ->assertCreated()
            ->assertJsonPath('data.es_credito', true);
        $this->getJson('/api/creditos/1/cuotas')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.1.fecha_vencimiento', '2099-02-28');
        $this->postJson('/api/creditos/pagos', ['cuota_id' => 1, 'monto' => '100.00'])
            ->assertOk();

        $this->assertSame(8300.0, (float) DB::table('creditos')->where('id', 1)->value('saldo'));
        $detalles = (new VentaDAO(DB::connection()->getPdo()))->detallesCreditos([1]);
        $this->assertCount(3, $detalles['cuotas'][1]);
        $this->assertCount(2, $detalles['pagos'][1]);
        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
        $this->assertSame(1, DB::table('ventas')->count());
        $this->postJson('/sesion/cerrar')->assertOk();
        $this->postJson('/api/ventas', $venta)->assertUnauthorized();
    }

    public function test_crud_de_clientes_con_mysql_y_permisos(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();

        $datos = [
            'dni' => '87654321',
            'nombres' => 'Ana',
            'apellidos' => 'Prueba',
        ];
        $this->postJson('/api/clientes', $datos)
            ->assertCreated()
            ->assertJsonPath('data.direccion', '');
        $this->getJson('/api/clientes/2')->assertOk()->assertJsonPath('data.nombres', 'Ana');

        $datos['nombres'] = 'Andrea';
        $this->putJson('/api/clientes/2', $datos)->assertOk()->assertJsonPath('data.nombres', 'Andrea');
        $this->postJson('/api/clientes', $datos)->assertUnprocessable();
        $this->deleteJson('/api/clientes/2')->assertOk();
        $this->getJson('/api/clientes/2')->assertNotFound();
    }
}
