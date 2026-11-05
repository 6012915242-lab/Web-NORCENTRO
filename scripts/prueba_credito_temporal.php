<?php
declare(strict_types=1);

// Las tablas temporales solo existen durante esta conexión y no modifican los datos reales.
require dirname(__DIR__) . '/autoload.php';

use App\AccesoDatos\VentaDAO;
use App\Configuracion\BaseDatos;
use App\Servicios\EvaluacionCreditoServicio;

$conexion = BaseDatos::conectar();
$tablas = [
    'CREATE TEMPORARY TABLE clientes (id INT PRIMARY KEY, estado VARCHAR(20))',
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

$conexion->exec("INSERT INTO clientes VALUES (1, 'Activo')");
$conexion->exec("INSERT INTO marcas VALUES (1, 'Activo')");
$conexion->exec("INSERT INTO motos VALUES (1, 1, 10000, 'Activo')");
$conexion->exec("INSERT INTO unidades_moto VALUES (1, 1, 'Disponible')");

$servicio = new EvaluacionCreditoServicio();
$dao = new VentaDAO($conexion);
$resultado = $servicio->procesarRegistroVenta($dao, [
    'cliente_id' => 1,
    'unidad_id' => 1,
    'modalidad' => 'Crédito',
    'descuento' => 0,
    'inicial' => 2000,
    'numero_cuotas' => 3,
    'fecha_primera_cuota' => '2099-01-31',
], 1);

$credito = $conexion->query('SELECT * FROM creditos')->fetch();
$cuotas = $conexion->query('SELECT * FROM cuotas ORDER BY numero')->fetchAll();
if (!$resultado['es_credito'] || (float) $credito['saldo'] !== 8400.0 || count($cuotas) !== 3 || $cuotas[1]['fecha_vencimiento'] !== '2099-02-28') {
    throw new RuntimeException('La venta o el cronograma no coinciden con lo esperado.');
}

$servicio->procesarPagoCuota($dao, ['cuota_id' => $cuotas[0]['id'], 'monto' => 100]);
$saldo = (float) $conexion->query('SELECT saldo FROM creditos')->fetchColumn();
if ($saldo !== 8300.0) {
    throw new RuntimeException('El abono no actualizó el saldo esperado.');
}

try {
    $servicio->procesarRegistroVenta($dao, [
        'cliente_id' => 1,
        'unidad_id' => 1,
        'modalidad' => 'Contado',
        'descuento' => 0,
    ], 1);
    throw new RuntimeException('La unidad vendida no debe poder venderse otra vez.');
} catch (DomainException $error) {
    if ((int) $conexion->query('SELECT COUNT(*) FROM ventas')->fetchColumn() !== 1) {
        throw new RuntimeException('La venta rechazada dejó datos incompletos.', 0, $error);
    }
}

echo "Venta, recargo, fechas y abono verificados en tablas temporales.\n";
