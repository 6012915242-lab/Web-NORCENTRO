<?php

namespace Tests\Feature;

use App\Configuracion\BaseDatos;
use App\AccesoDatos\VentaDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\OfertaDAO;
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
            'CREATE TEMPORARY TABLE marcas (id INT PRIMARY KEY, nombre VARCHAR(100), estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE motos (id INT AUTO_INCREMENT PRIMARY KEY, marca_id INT, modelo VARCHAR(120), cilindrada INT, color VARCHAR(60), precio DECIMAL(10,2), descripcion VARCHAR(255), anio INT, imagen VARCHAR(255), estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE unidades_moto (id INT AUTO_INCREMENT PRIMARY KEY, moto_id INT, codigo_serie VARCHAR(50) UNIQUE, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE moto_colores (id INT AUTO_INCREMENT PRIMARY KEY, moto_id INT, nombre VARCHAR(60), codigo_hex VARCHAR(7), orden INT, UNIQUE KEY (moto_id, nombre))',
            'CREATE TEMPORARY TABLE moto_color_imagenes (id INT AUTO_INCREMENT PRIMARY KEY, color_id INT, ruta VARCHAR(255), texto_alt VARCHAR(10), orden INT)',
            'CREATE TEMPORARY TABLE ofertas (id INT AUTO_INCREMENT PRIMARY KEY, moto_id INT, titulo VARCHAR(100), descripcion VARCHAR(255) NOT NULL DEFAULT "", precio_normal DECIMAL(10,2), precio_oferta DECIMAL(10,2), fecha_inicio DATE, fecha_fin DATE, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE pedidos (id INT AUTO_INCREMENT PRIMARY KEY, request_id VARCHAR(36) UNIQUE, nombres VARCHAR(100), apellidos VARCHAR(100), dni CHAR(8), telefono VARCHAR(20), correo VARCHAR(120), inicial DECIMAL(10,2), numero_cuotas INT, subtotal DECIMAL(10,2), total DECIMAL(10,2), modalidad VARCHAR(20), estado VARCHAR(40), creado_en DATETIME, credito_autorizado_en DATETIME, vence_en DATETIME, checkout_url TEXT, mp_order_id VARCHAR(120))',
            'CREATE TEMPORARY TABLE pedido_items (id INT AUTO_INCREMENT PRIMARY KEY, pedido_id INT, moto_id INT, color_id INT, cantidad INT, precio_unitario DECIMAL(10,2), subtotal DECIMAL(10,2))',
            'CREATE TEMPORARY TABLE pedido_unidades (pedido_item_id INT, unidad_id INT)',
            'CREATE TEMPORARY TABLE ventas (id INT AUTO_INCREMENT PRIMARY KEY, cliente_id INT, usuario_id INT, unidad_id INT, precio DECIMAL(10,2), descuento DECIMAL(10,2), total DECIMAL(10,2), modalidad VARCHAR(20), estado VARCHAR(20) DEFAULT "Confirmada")',
            'CREATE TEMPORARY TABLE solicitud_credito_ventas (pedido_item_id INT NOT NULL, venta_id INT PRIMARY KEY)',
            'CREATE TEMPORARY TABLE creditos (id INT AUTO_INCREMENT PRIMARY KEY, venta_id INT, inicial DECIMAL(10,2), monto_financiado DECIMAL(10,2), numero_cuotas INT, saldo DECIMAL(10,2), estado VARCHAR(20), porcentaje_recargo DECIMAL(5,2), importe_recargo DECIMAL(10,2))',
            'CREATE TEMPORARY TABLE cuotas (id INT AUTO_INCREMENT PRIMARY KEY, credito_id INT, numero INT, fecha_vencimiento DATE, monto DECIMAL(10,2), pagado DECIMAL(10,2) DEFAULT 0, estado VARCHAR(20))',
            'CREATE TEMPORARY TABLE pagos (id INT AUTO_INCREMENT PRIMARY KEY, credito_id INT, cuota_id INT NULL, monto DECIMAL(10,2), fecha DATE, concepto VARCHAR(40))',
        ];

        foreach ($tablas as $tabla) {
            $conexion->exec($tabla);
        }

        $conexion->exec("INSERT INTO vista_usuarios_permisos VALUES
            (1, 'ventas.registrar'), (1, 'creditos.revisar'),
            (1, 'clientes.gestionar'), (1, 'clientes.desactivar'),
            (1, 'ofertas.gestionar'), (1, 'inventario.gestionar')");
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
        $conexion->exec("INSERT INTO marcas VALUES (1, 'Bajaj', 'Activo')");
        $conexion->exec("INSERT INTO motos (id, marca_id, modelo, precio, estado) VALUES (1, 1, 'Pulsar', 10000, 'Activo')");
        $conexion->exec("INSERT INTO unidades_moto (id, moto_id, estado) VALUES (1, 1, 'Disponible')");
        $conexion->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

    }

    public function test_pedido_de_dos_colores_reserva_unidades_distintas(): void
    {
        DB::table('unidades_moto')->insert(['id' => 2, 'moto_id' => 1, 'estado' => 'Disponible']);
        DB::table('moto_colores')->insert([
            ['id' => 1, 'moto_id' => 1, 'nombre' => 'Rojo'],
            ['id' => 2, 'moto_id' => 1, 'nombre' => 'Azul'],
        ]);

        $pedido = (new PedidoDAO(DB::connection()->getPdo()))->crear($this->datosPedido());

        $this->assertSame(20000.0, $pedido['total']);
        $this->assertSame(2, DB::table('pedido_unidades')->distinct()->count('unidad_id'));
        $this->assertSame(2, DB::table('unidades_moto')->where('estado', 'Reservada')->count());
    }

    public function test_catalogo_detalle_y_pedido_eligen_la_misma_oferta_mas_economica(): void
    {
        DB::table('ofertas')->insert([
            ['moto_id' => 1, 'titulo' => 'Primera', 'precio_normal' => 10000, 'precio_oferta' => 9500,
                'fecha_inicio' => '2000-01-01', 'fecha_fin' => '2098-01-01', 'estado' => 'Activa'],
            ['moto_id' => 1, 'titulo' => 'Mejor precio', 'precio_normal' => 10000, 'precio_oferta' => 9000,
                'fecha_inicio' => '2000-01-01', 'fecha_fin' => '2099-01-01', 'estado' => 'Activa'],
        ]);
        $conexion = DB::connection()->getPdo();
        $ofertas = new OfertaDAO($conexion);
        $datos = $this->datosPedido();
        $datos['items'] = [['moto_id' => 1, 'color_id' => null, 'cantidad' => 1]];

        $this->assertSame(9000.0, (float) $ofertas->activas()[0]['precio_oferta']);
        $this->assertSame(9000.0, (float) $ofertas->activaParaMoto(1)['precio_oferta']);
        $this->assertSame(9000.0, (new PedidoDAO($conexion))->crear($datos)['total']);
    }

    public function test_reintento_del_pedido_solo_acepta_los_mismos_datos(): void
    {
        $dao = new PedidoDAO(DB::connection()->getPdo());
        $datos = $this->datosPedido();
        $datos['items'] = [['moto_id' => 1, 'color_id' => null, 'cantidad' => 1]];
        $primero = $dao->crear($datos);
        $repetido = $dao->crear($datos);

        $this->assertSame($primero['id'], $repetido['id']);
        $this->assertTrue($repetido['repetido']);

        $datos['modalidad'] = 'Crédito';
        try {
            $dao->crear($datos);
            $this->fail('El cambio de modalidad debía rechazarse.');
        } catch (\DomainException $error) {
            $this->assertStringContainsString('cambió', $error->getMessage());
        }

        $datos['modalidad'] = 'Contado';
        $datos['items'][0]['cantidad'] = 2;
        try {
            $dao->crear($datos);
            $this->fail('El cambio de cantidad debía rechazarse.');
        } catch (\DomainException $error) {
            $this->assertStringContainsString('productos cambiaron', $error->getMessage());
        }

        $this->assertSame(1, DB::table('pedidos')->count());
        $this->assertSame(1, DB::table('pedido_unidades')->count());
    }

    public function test_pedido_de_dos_colores_sin_stock_suficiente_no_reserva(): void
    {
        DB::table('moto_colores')->insert([
            ['id' => 1, 'moto_id' => 1, 'nombre' => 'Rojo'],
            ['id' => 2, 'moto_id' => 1, 'nombre' => 'Azul'],
        ]);

        try {
            (new PedidoDAO(DB::connection()->getPdo()))->crear($this->datosPedido());
            $this->fail('El pedido debía rechazarse por falta de stock.');
        } catch (\DomainException $error) {
            $this->assertStringContainsString('No hay suficientes unidades', $error->getMessage());
        }

        $this->assertSame(0, DB::table('pedidos')->count());
        $this->assertSame('Disponible', DB::table('unidades_moto')->where('id', 1)->value('estado'));
    }

    private function datosPedido(): array
    {
        return [
            'request_id' => 'pedido-prueba-001',
            'nombres' => 'Ana',
            'apellidos' => 'Prueba',
            'dni' => '87654321',
            'telefono' => '900123456',
            'correo' => 'ana@example.com',
            'modalidad' => 'Contado',
            'items' => [
                ['moto_id' => 1, 'color_id' => 1, 'cantidad' => 1],
                ['moto_id' => 1, 'color_id' => 2, 'cantidad' => 1],
            ],
        ];
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

    public function test_solicitud_aprobada_admite_ventas_parciales_sin_superar_la_cantidad(): void
    {
        DB::table('pedidos')->insert([
            'id' => 1, 'dni' => '11111111', 'modalidad' => 'Crédito', 'estado' => 'Crédito aprobado',
        ]);
        DB::table('pedido_items')->insert(['id' => 1, 'pedido_id' => 1, 'moto_id' => 1, 'cantidad' => 2]);
        DB::table('unidades_moto')->insert([
            ['id' => 2, 'moto_id' => 1, 'estado' => 'Disponible'],
            ['id' => 3, 'moto_id' => 1, 'estado' => 'Disponible'],
        ]);
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])->assertOk();
        $venta = [
            'cliente_id' => 1, 'unidad_id' => 1, 'pedido_id' => 1, 'modalidad' => 'Crédito',
            'descuento' => 0, 'inicial' => 2000, 'numero_cuotas' => 3,
            'fecha_primera_cuota' => '2099-01-31',
        ];
        DB::table('clientes')->insert([
            'id' => 2, 'dni' => '22222222', 'nombres' => 'Otro', 'apellidos' => 'Cliente', 'estado' => 'Activo',
        ]);
        $venta['cliente_id'] = 2;
        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
        $this->assertSame(0, DB::table('solicitud_credito_ventas')->count());
        $venta['cliente_id'] = 1;
        DB::table('motos')->insert(['id' => 2, 'marca_id' => 1, 'modelo' => 'Otro modelo', 'precio' => 8000, 'estado' => 'Activo']);
        DB::table('unidades_moto')->insert(['id' => 4, 'moto_id' => 2, 'estado' => 'Disponible']);
        $venta['unidad_id'] = 4;
        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
        $this->assertSame('Disponible', DB::table('unidades_moto')->where('id', 4)->value('estado'));
        $venta['unidad_id'] = 1;
        $this->postJson('/api/ventas', $venta)->assertCreated();
        $venta['unidad_id'] = 2;
        $this->postJson('/api/ventas', $venta)->assertCreated();
        $venta['unidad_id'] = 3;
        $this->postJson('/api/ventas', $venta)->assertUnprocessable();
        $this->assertSame(2, DB::table('solicitud_credito_ventas')->count());
        $this->assertSame('Disponible', DB::table('unidades_moto')->where('id', 3)->value('estado'));

        DB::table('ventas')->where('id', 1)->update(['estado' => 'Anulada']);
        $this->postJson('/api/ventas', $venta)->assertCreated();
        $this->assertSame(3, DB::table('solicitud_credito_ventas')->count());
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

    public function test_bloquea_la_cuenta_despues_de_cinco_claves_incorrectas(): void
    {
        for ($intento = 1; $intento <= 4; $intento++) {
            $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'incorrecta'])
                ->assertUnprocessable();
        }

        $this->assertNull(DB::table('usuarios')->where('id', 1)->value('bloqueado_hasta'));
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'incorrecta'])
            ->assertUnprocessable();
        $this->assertNotNull(DB::table('usuarios')->where('id', 1)->value('bloqueado_hasta'));
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertUnprocessable();
        $this->assertGuest();
    }

    public function test_deniega_ventas_cuando_falta_el_permiso(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();
        DB::table('vista_usuarios_permisos')->where('permiso', 'ventas.registrar')->delete();

        $this->postJson('/api/ventas', [
            'cliente_id' => 1,
            'unidad_id' => 1,
            'modalidad' => 'Contado',
            'descuento' => 0,
        ])->assertForbidden();

        $this->assertSame(0, DB::table('ventas')->count());
        DB::table('creditos')->insert(['id' => 1, 'venta_id' => 1, 'estado' => 'Activo']);
        $this->getJson('/api/creditos/1/cuotas')->assertOk();
    }

    public function test_gestiona_ofertas_con_las_mismas_reglas_en_mysql(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();
        $datos = [
            'moto_id' => 1,
            'titulo' => 'Promoción',
            'precio_normal' => '10000.00',
            'precio_oferta' => '9000.00',
            'fecha_inicio' => '2099-01-01',
            'fecha_fin' => '2099-01-31',
        ];

        $this->postJson('/api/ofertas', $datos)
            ->assertCreated()
            ->assertJsonPath('data.descripcion', '');
        $datos['titulo'] = 'Promoción actualizada';
        $this->putJson('/api/ofertas/1', $datos)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Promoción actualizada');
        $this->getJson('/api/ofertas')
            ->assertOk()
            ->assertJsonPath('data.0.moto.marca.nombre', 'Bajaj');
        $this->deleteJson('/api/ofertas/1')->assertOk();
        $this->assertSame('Inactiva', DB::table('ofertas')->where('id', 1)->value('estado'));
    }

    public function test_registra_inventario_con_el_dao_y_los_permisos(): void
    {
        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();

        $datos = ['moto_id' => 1, 'codigo_serie' => '  moto-123  '];
        $this->postJson('/api/inventario', $datos)
            ->assertCreated()
            ->assertJsonPath('data.codigo_serie', 'MOTO-123');
        $this->postJson('/api/inventario', $datos)->assertUnprocessable();
        $this->assertSame(1, DB::table('unidades_moto')->where('codigo_serie', 'MOTO-123')->count());

        DB::table('marcas')->where('id', 1)->update(['estado' => 'Inactivo']);
        $datos['codigo_serie'] = 'MOTO-456';
        $this->postJson('/api/inventario', $datos)->assertUnprocessable();
    }

    public function test_evalua_una_solicitud_de_credito_sin_repetirla(): void
    {
        DB::table('pedidos')->insert([
            'id' => 1,
            'nombres' => 'Ana',
            'apellidos' => 'Prueba',
            'dni' => '87654321',
            'telefono' => '900123456',
            'correo' => 'ana@example.com',
            'inicial' => 1000,
            'numero_cuotas' => 6,
            'total' => 10000,
            'modalidad' => 'Crédito',
            'estado' => 'En revisión de crédito',
            'creado_en' => '2026-01-01 12:00:00',
            'credito_autorizado_en' => '2026-01-01 12:00:00',
        ]);
        DB::table('pedido_items')->insert(['pedido_id' => 1, 'moto_id' => 1, 'cantidad' => 1]);

        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();
        $this->getJson('/api/solicitudes-credito')
            ->assertOk()
            ->assertJsonPath('data.0.dni', '87654321');
        $this->postJson('/api/solicitudes-credito/1/evaluacion', ['decision' => 'aprobar'])
            ->assertOk();
        $this->assertSame('Crédito aprobado', DB::table('pedidos')->where('id', 1)->value('estado'));
        $this->assertSame(1, DB::table('clientes')->where('dni', '87654321')->count());
        $this->postJson('/api/solicitudes-credito/1/evaluacion', ['decision' => 'aprobar'])
            ->assertUnprocessable();
    }

    public function test_rechaza_solicitud_sin_crear_un_cliente(): void
    {
        DB::table('pedidos')->insert([
            'id' => 1,
            'nombres' => 'Ana',
            'apellidos' => 'Prueba',
            'dni' => '87654321',
            'telefono' => '900123456',
            'correo' => 'ana@example.com',
            'modalidad' => 'Crédito',
            'estado' => 'En revisión de crédito',
        ]);

        $this->postJson('/sesion/iniciar', ['usuario' => 'prueba', 'password' => 'clave-prueba'])
            ->assertOk();
        $this->postJson('/api/solicitudes-credito/1/evaluacion', ['decision' => 'rechazar'])
            ->assertOk();
        $this->assertSame('Crédito no aprobado', DB::table('pedidos')->where('id', 1)->value('estado'));
        $this->assertSame(0, DB::table('clientes')->where('dni', '87654321')->count());
    }

    public function test_guarda_la_moto_y_su_galeria_en_una_sola_transaccion(): void
    {
        $datos = [
            'marca_id' => 1,
            'modelo' => 'Pulsar',
            'cilindrada' => 160,
            'color' => 'Rojo',
            'precio' => 9000,
            'descripcion' => 'Moto urbana',
            'anio' => 2026,
            'imagen' => 'moto.jpg',
            'cantidad' => 1,
        ];
        $colores = [
            [
                'nombre' => 'Rojo',
                'codigo_hex' => '#ff0000',
                'imagenes' => [
                    ['ruta' => 'frente.jpg', 'texto_alt' => 'Frente'],
                    ['ruta' => 'lado.jpg', 'texto_alt' => 'Lado'],
                ],
            ],
            [
                'nombre' => 'Azul',
                'codigo_hex' => '#0000ff',
                'imagenes' => [['ruta' => 'azul.jpg', 'texto_alt' => 'Azul']],
            ],
        ];
        $dao = new MotoDAO(DB::connection()->getPdo());
        $id = $dao->guardarConGaleria($datos, $colores);

        $this->assertSame('Pulsar', DB::table('motos')->where('id', $id)->value('modelo'));
        $this->assertSame(3, DB::table('moto_color_imagenes')->count());
        $this->assertCount(2, $dao->coloresPublicos($id));

        $colores[0]['imagenes'][0]['texto_alt'] = str_repeat('x', 20);
        $datos['modelo'] = 'Otra moto';
        try {
            $dao->guardarConGaleria($datos, $colores);
            $this->fail('La galería inválida debía rechazar la operación.');
        } catch (\PDOException) {
            $this->assertSame(0, DB::table('motos')->where('modelo', 'Otra moto')->count());
            $this->assertSame(2, DB::table('unidades_moto')->count());
            $this->assertSame(2, DB::table('moto_colores')->count());
        }

        try {
            $dao->actualizarConGaleria($id, $datos, $colores);
            $this->fail('La edición con una galería inválida debía rechazarse.');
        } catch (\PDOException) {
            $this->assertSame('Pulsar', DB::table('motos')->where('id', $id)->value('modelo'));
            $this->assertSame(3, DB::table('moto_color_imagenes')->count());
        }
    }
}
