<?php

declare(strict_types=1);
// Endpoint JSON: recibe las solicitudes del carrito.
require_once dirname(__DIR__, 2) . '/autoload.php';

use App\AccesoDatos\PedidoDAO;
use App\Configuracion\BaseDatos;
use App\Middlewares\SeguridadMiddleware;
use App\Servicios\PagoMercadoPago;
use App\Servicios\ValidacionPedidoServicio;

SeguridadMiddleware::inicializarEntornoSeguro(dirname(__DIR__, 2) . '/storage/sessions');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function responder(int $estado, array $datosRecibidos): never
{
    http_response_code($estado);
    echo json_encode($datosRecibidos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
try {
    $conexion = BaseDatos::conectar();
    $pedidos = new PedidoDAO($conexion);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idSolicitud = $_GET['request_id'] ?? null;
        if (!is_string($idSolicitud) || !preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)) {
            responder(400, ['ok' => false,
                'message' => 'Pedido no válido.']);
        }
        $pedidos->liberarPedidosVencidos();
        $pedido = $pedidos->obtenerPorRequest($idSolicitud);
        if (! $pedido) {
            responder(404, ['ok' => false,
                'message' => 'No encontramos el pedido.']);
        }
        if ($pedido['modalidad'] === 'Contado' && $pedido['mp_order_id'] && getenv('MP_ACCESS_TOKEN')) {
            $ordenRemota = (new PagoMercadoPago)->obtenerOrden((string) $pedido['mp_order_id']);
            if (($ordenRemota['external_reference'] ?? '') === 'NORCENTRO-'.$pedido['id'] && abs((float) ($ordenRemota['total_amount'] ?? 0) - (float) $pedido['total']) < 0.01) {
                $estado = (string) ($ordenRemota['status'] ?? '');
                $montoPagado = (float) ($ordenRemota['total_paid_amount'] ?? 0);
                if ($estado === 'processed' && $montoPagado + 0.009 >= (float) $pedido['total']) {
                    $pedidos->actualizarEstadoPago((int) $pedido['id'], (string) $pedido['mp_order_id'], 'processed');
                } elseif (in_array($estado, ['canceled',
                    'expired'], true)) {
                    $pedidos->actualizarEstadoPago((int) $pedido['id'], (string) $pedido['mp_order_id'], $estado);
                }
            }
            $pedido = $pedidos->obtenerPorRequest($idSolicitud);
        }
        responder(200, ['ok' => true,
            'pedido_id' => (int) $pedido['id'],
            'modalidad' => $pedido['modalidad'],
            'total' => (float) $pedido['total'],
            'estado' => $pedido['estado']]);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(405, ['ok' => false,
            'message' => 'Método no permitido.']);
    }
    $datosRecibidos = json_decode((string) file_get_contents('php://input'), true);
    if (! is_array($datosRecibidos)) {
        responder(400, ['ok' => false,
            'message' => 'Solicitud JSON no válida.']);
    }
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $datosRecibidos['csrf'] ?? null;
    $tokenSesion = $_SESSION['_csrf'] ?? null;
    if (
        !is_string($csrf)
        || !is_string($tokenSesion)
        || $csrf === ''
        || $tokenSesion === ''
        || !hash_equals($tokenSesion, $csrf)
    ) {
        responder(403, ['ok' => false,
            'message' => 'La sesión venció. Recarga la página e inténtalo otra vez.']);
    }
    $datosPedido = (new ValidacionPedidoServicio())->validar($datosRecibidos);
    $modalidad = $datosPedido['modalidad'];
    $idSolicitud = $datosPedido['request_id'];
    $correo = $datosPedido['correo'];

    if ($modalidad === 'Contado' && trim(getenv('MP_ACCESS_TOKEN') ?: '') === '') {
        responder(503, [
            'ok' => false,
            'message' => 'Mercado Pago aún no está configurado. El administrador debe cargar MP_ACCESS_TOKEN en el servidor antes de cobrar.',
        ]);
    }

    $pedido = $pedidos->crear($datosPedido);
    if (in_array($pedido['estado'], ['Pagado',
        'Cancelado'], true)) {
        responder(409, ['ok' => false,
            'message' => 'Este pedido ya fue pagado o venció. Actualiza el carrito para crear otro pedido.']);
    }
    if ($modalidad === 'Crédito') {
        responder(201, ['ok' => true,
            'pedido_id' => $pedido['id'],
            'modalidad' => 'Crédito',
            'estado' => $pedido['estado'],
            'total' => $pedido['total'],
            'message' => 'Solicitud registrada para revisión de un administrador o vendedor de Norcentro.']);
    }
    if ($pedido['checkout_url']) {
        responder(200, ['ok' => true,
            'pedido_id' => $pedido['id'],
            'modalidad' => 'Contado',
            'estado' => $pedido['estado'],
            'checkout_url' => $pedido['checkout_url']]);
    }
    $resultadoPago = (new PagoMercadoPago)->crearOrden(['id' => $pedido['id'],
        'request_id' => $idSolicitud,
        'correo' => $correo,
        'total' => $pedido['total'],
        'items' => $pedido['items']]);
    $pedidos->guardarMercadoPago((int) $pedido['id'], $resultadoPago['mp_order_id'], $resultadoPago['checkout_url']);
    responder(201, ['ok' => true,
        'pedido_id' => $pedido['id'],
        'modalidad' => 'Contado',
        'estado' => 'Pendiente de pago',
        'checkout_url' => $resultadoPago['checkout_url']]);
} catch (DomainException $e) {
    responder(409, ['ok' => false,
        'message' => $e->getMessage()]);
} catch (InvalidArgumentException $e) {
    responder(422, ['ok' => false,
        'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log((string) $e);
    responder(502, ['ok' => false,
        'message' => 'No se pudo completar el pedido. Revisa la conexión con Mercado Pago y vuelve a intentarlo.']);
}
