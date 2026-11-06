<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/autoload.php';

use App\AccesoDatos\AuditoriaDAO;
use App\Configuracion\BaseDatos;
use App\Controladores\ConsultaDniControlador;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\ApiMiddleware;
use App\Middlewares\SeguridadMiddleware;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

ApiMiddleware::exigirMetodo(['POST']);
SeguridadMiddleware::inicializarEntornoSeguro(dirname(__DIR__, 2).'/storage/sessions');
ApiMiddleware::validarCsrfJson($_POST['_csrf'] ?? null, 'La sesión del formulario venció. Recarga la página.');

try {
    $conexion = BaseDatos::conectar();
    $usuario = ApiMiddleware::exigirSesionJson($conexion, 'Inicia sesión para consultar un DNI.');
    ApiMiddleware::exigirPermisoJson($conexion, $usuario, 'clientes.gestionar', 'No tienes permiso para consultar clientes.');

    (new AuditoriaMiddleware(new AuditoriaDAO($conexion)))->registrar($usuario, 'consultar_dni', 'clientes');
    $datos = SeguridadMiddleware::sanearArregloEntrada($_POST);
    session_write_close();
    (new ConsultaDniControlador)->consultar($datos);
} catch (Throwable $error) {
    error_log('Error al iniciar consulta de DNI: '.get_class($error));
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'No se pudo iniciar la consulta.'
    ]);
}
