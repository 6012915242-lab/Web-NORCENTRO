<?php

declare(strict_types=1);

/*
 * Endpoint JSON: POST api/consulta-dni.php
 * Completa los datos de un cliente a partir de su DNI (proveedor Factiliza).
 * Requiere sesión iniciada, token CSRF y el permiso clientes.gestionar.
 */

require_once dirname(__DIR__, 2).'/autoload.php';

use App\AccesoDatos\AuditoriaDAO;
use App\Configuracion\BaseDatos;
use App\Controladores\ConsultaDniControlador;
use App\Middlewares\ApiMiddleware;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\SeguridadMiddleware;

ApiMiddleware::iniciar();
ApiMiddleware::exigirMetodo(['POST']);
ApiMiddleware::validarCsrf($_POST['_csrf'] ?? null, 'La sesión del formulario venció. Recarga la página.');

try {
    $conexion = BaseDatos::conectar();
    $usuario = ApiMiddleware::exigirSesion($conexion, 'Inicia sesión para consultar un DNI.');
    ApiMiddleware::exigirPermiso($conexion, $usuario, 'clientes.gestionar', 'No tienes permiso para consultar clientes.');

    (new AuditoriaMiddleware(new AuditoriaDAO($conexion)))->registrar($usuario, 'consultar_dni', 'clientes');
    $datos = SeguridadMiddleware::sanearArregloEntrada($_POST);

    // La consulta externa puede tardar: se libera la sesión para no bloquear otras pestañas.
    session_write_close();
    (new ConsultaDniControlador)->consultar($datos);
} catch (Throwable $error) {
    error_log('Error al iniciar consulta de DNI: '.get_class($error));
    ApiMiddleware::error(500, 'No se pudo iniciar la consulta.');
}
