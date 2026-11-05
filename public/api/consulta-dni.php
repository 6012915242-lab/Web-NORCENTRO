<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/autoload.php';

use App\AccesoDatos\AuditoriaDAO;
use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use App\Configuracion\BaseDatos;
use App\Controladores\ConsultaDniControlador;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\SeguridadMiddleware;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

SeguridadMiddleware::inicializarEntornoSeguro(dirname(__DIR__, 2) . '/storage/sessions');
$tokenSesion = $_SESSION['_csrf'] ?? null;
$tokenRecibido = $_POST['_csrf'] ?? null;
if (
    !is_string($tokenSesion)
    || !is_string($tokenRecibido)
    || $tokenSesion === ''
    || $tokenRecibido === ''
    || !hash_equals($tokenSesion, $tokenRecibido)
) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'mensaje' => 'La sesión del formulario venció. Recarga la página.']);
    exit;
}

try {
    $conexion = BaseDatos::conectar();
    $usuario = AutenticacionMiddleware::validarSesionPersistida(
        $_SESSION['user'] ?? null,
        new SesionDAO($conexion)
    );
    if ($usuario === null) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Inicia sesión para consultar un DNI.']);
        exit;
    }

    $usuarioId = (int) ($usuario['id'] ?? 0);
    if (!(new PermisoDAO($conexion))->usuarioTiene($usuarioId, 'clientes.gestionar')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para consultar clientes.']);
        exit;
    }

    (new AuditoriaMiddleware(new AuditoriaDAO($conexion)))->registrar($usuario, 'consultar_dni', 'clientes');
    $datos = SeguridadMiddleware::sanearArregloEntrada($_POST);
    session_write_close();
    (new ConsultaDniControlador())->consultar($datos);
} catch (Throwable $error) {
    error_log('Error al iniciar consulta de DNI: ' . get_class($error));
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo iniciar la consulta.']);
}
