<?php

declare(strict_types=1);

/*
 * Endpoint JSON del carrito de compras (público, no requiere sesión).
 *  GET  api/pedidos.php?request_id=...  consulta el estado de un pedido.
 *  POST api/pedidos.php                 registra un pedido al contado (Mercado Pago)
 *                                       o una solicitud de crédito. Exige token CSRF.
 */

require_once dirname(__DIR__, 2).'/autoload.php';

use App\AccesoDatos\PedidoDAO;
use App\Configuracion\BaseDatos;
use App\Middlewares\ApiMiddleware;
use App\Servicios\PagoMercadoPago;
use App\Servicios\ValidacionPedidoServicio;

ApiMiddleware::iniciar();
ApiMiddleware::exigirMetodo(['GET', 'POST']);

try {
    $conexion = BaseDatos::conectar();
    $pedidos = new PedidoDAO($conexion);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $idSolicitud = $_GET['request_id'] ?? null;
        if (! is_string($idSolicitud) || ! preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)) {
            ApiMiddleware::responder(400, [
                'ok' => false,
                'mensaje' => 'Pedido no válido.',
            ]);
        }
        $pedidos->liberarPedidosVencidos();
        $pedido = $pedidos->obtenerPorRequest($idSolicitud);
        if (! $pedido) {
            ApiMiddleware::responder(404, [
                'ok' => false,
                'mensaje' => 'No encontramos el pedido.',
            ]);
        }
        if ($pedido['modalidad'] === 'Contado' && $pedido['mp_order_id'] && getenv('MP_ACCESS_TOKEN')) {
            $ordenRemota = (new PagoMercadoPago)->obtenerOrden((string) $pedido['mp_order_id']);
            $referenciaCoincide = ($ordenRemota['external_reference'] ?? '') === 'NORCENTRO-'.$pedido['id'];
            $importeCoincide = abs((float) ($ordenRemota['total_amount'] ?? 0) - (float) $pedido['total']) < 0.01;
            if ($referenciaCoincide && $importeCoincide) {
                $estado = (string) ($ordenRemota['status'] ?? '');
                $montoPagado = (float) ($ordenRemota['total_paid_amount'] ?? 0);
                if ($estado === 'processed' && $montoPagado + 0.009 >= (float) $pedido['total']) {
                    $pedidos->actualizarEstadoPago((int) $pedido['id'], (string) $pedido['mp_order_id'], 'processed');
                } elseif (in_array($estado, ['canceled', 'expired'], true)) {
                    $pedidos->actualizarEstadoPago((int) $pedido['id'], (string) $pedido['mp_order_id'], $estado);
                }
            }
            $pedido = $pedidos->obtenerPorRequest($idSolicitud);
        }
        ApiMiddleware::responder(200, [
            'ok' => true,
            'pedido_id' => (int) $pedido['id'],
            'modalidad' => $pedido['modalidad'],
            'total' => (float) $pedido['total'],
            'estado' => $pedido['estado'],
        ]);
    }
    $datosRecibidos = json_decode((string) file_get_contents('php://input'), true);
    if (! is_array($datosRecibidos)) {
        ApiMiddleware::responder(400, [
            'ok' => false,
            'mensaje' => 'Solicitud JSON no válida.',
        ]);
    }
    ApiMiddleware::validarCsrf(
        $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $datosRecibidos['csrf'] ?? null,
        'La sesión venció. Recarga la página e inténtalo otra vez.'
    );
    $datosPedido = (new ValidacionPedidoServicio)->validar($datosRecibidos);
    $modalidad = $datosPedido['modalidad'];
    $idSolicitud = $datosPedido['request_id'];
    $correo = $datosPedido['correo'];

    if ($modalidad === 'Contado' && trim(getenv('MP_ACCESS_TOKEN') ?: '') === '') {
        ApiMiddleware::responder(503, [
            'ok' => false,
            'mensaje' => 'Mercado Pago aún no está configurado. El administrador debe cargar MP_ACCESS_TOKEN en el servidor antes de cobrar.',
        ]);
    }

    $pedido = $pedidos->crear($datosPedido);
    if (in_array($pedido['estado'], ['Pagado', 'Cancelado'], true)) {
        ApiMiddleware::responder(409, [
            'ok' => false,
            'mensaje' => 'Este pedido ya fue pagado o venció. Actualiza el carrito para crear otro pedido.',
        ]);
    }
    if ($modalidad === 'Crédito') {
        ApiMiddleware::responder(201, [
            'ok' => true,
            'pedido_id' => $pedido['id'],
            'modalidad' => 'Crédito',
            'estado' => $pedido['estado'],
            'total' => $pedido['total'],
            'mensaje' => 'Solicitud registrada para revisión de un administrador o vendedor de Norcentro.',
        ]);
    }
    if ($pedido['checkout_url']) {
        ApiMiddleware::responder(200, [
            'ok' => true,
            'pedido_id' => $pedido['id'],
            'modalidad' => 'Contado',
            'estado' => $pedido['estado'],
            'checkout_url' => $pedido['checkout_url'],
        ]);
    }
    $resultadoPago = (new PagoMercadoPago)->crearOrden([
        'id' => $pedido['id'],
        'request_id' => $idSolicitud,
        'correo' => $correo,
        'total' => $pedido['total'],
        'items' => $pedido['items'],
    ]);
    $pedidos->guardarMercadoPago((int) $pedido['id'], $resultadoPago['mp_order_id'], $resultadoPago['checkout_url']);
    ApiMiddleware::responder(201, [
        'ok' => true,
        'pedido_id' => $pedido['id'],
        'modalidad' => 'Contado',
        'estado' => 'Pendiente de pago',
        'checkout_url' => $resultadoPago['checkout_url'],
    ]);
} catch (DomainException $error) {
    ApiMiddleware::responder(409, ['ok' => false, 'mensaje' => $error->getMessage()]);
} catch (InvalidArgumentException $error) {
    ApiMiddleware::responder(422, ['ok' => false, 'mensaje' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log((string) $error);
    ApiMiddleware::responder(502, [
        'ok' => false,
        'mensaje' => 'No se pudo completar el pedido. Revisa la conexión con Mercado Pago y vuelve a intentarlo.',
    ]);
}
