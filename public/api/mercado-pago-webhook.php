<?php

declare(strict_types=1);

/*
 * Webhook de Mercado Pago: recibe los avisos de pago y actualiza el pedido.
 * No usa sesión; la autenticidad se comprueba con la firma HMAC (x-signature).
 */

require_once dirname(__DIR__, 2).'/autoload.php';

use App\AccesoDatos\PedidoDAO;
use App\Configuracion\BaseDatos;
use App\Middlewares\ApiMiddleware;
use App\Servicios\PagoMercadoPago;

header('Content-Type: application/json; charset=utf-8');

try {
    $claveSecreta = trim(getenv('MP_WEBHOOK_SECRET') ?: '');
    if ($claveSecreta === '') {
        ApiMiddleware::error(503, 'Falta configurar la firma secreta del webhook.');
    }
    $idDato = strtolower((string) ($_GET['data.id'] ?? $_GET['data_id'] ?? ''));
    $idSolicitud = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
    $firma = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    if ($idDato === '' || $idSolicitud === '' || $firma === '') {
        ApiMiddleware::error(401, 'Notificación incompleta.');
    }
    $partesFirma = [];
    foreach (explode(',', $firma) as $fragmento) {
        $par = explode('=', trim($fragmento), 2);
        if (count($par) === 2) {
            $partesFirma[$par[0]] = $par[1];
        }
    }
    if (empty($partesFirma['ts']) || empty($partesFirma['v1'])) {
        ApiMiddleware::error(401, 'Firma inválida.');
    }
    $manifiesto = 'id:'.$idDato.';request-id:'.$idSolicitud.';ts:'.$partesFirma['ts'].';';
    $esperado = hash_hmac('sha256', $manifiesto, $claveSecreta);
    if (! hash_equals($esperado, $partesFirma['v1'])) {
        ApiMiddleware::error(401, 'Firma inválida.');
    }
    $tipo = (string) ($_GET['type'] ?? '');
    if ($tipo !== '' && ! in_array($tipo, ['order', 'orders'], true)) {
        ApiMiddleware::responder(200, ['ok' => true, 'ignored' => true]);
    }
    $ordenRemota = (new PagoMercadoPago)->obtenerOrden($idDato);
    $pedidoDAO = new PedidoDAO(BaseDatos::conectar());
    $pedido = $pedidoDAO->obtenerPorMercadoPago($idDato);
    if (
        ! $pedido
        || ($ordenRemota['external_reference'] ?? '') !== 'NORCENTRO-'.$pedido['id']
        || abs((float) ($ordenRemota['total_amount'] ?? 0) - (float) $pedido['total']) >= 0.01
    ) {
        ApiMiddleware::error(404, 'La notificación no coincide con un pedido registrado.');
    }
    $estado = (string) ($ordenRemota['status'] ?? '');
    $montoPagado = (float) ($ordenRemota['total_paid_amount'] ?? 0);
    if ($estado === 'processed' && $montoPagado + 0.009 >= (float) $pedido['total']) {
        $pedidoDAO->actualizarEstadoPago((int) $pedido['id'], $idDato, 'processed');
    } elseif (in_array($estado, ['canceled', 'expired'], true)) {
        $pedidoDAO->actualizarEstadoPago((int) $pedido['id'], $idDato, $estado);
    }
    ApiMiddleware::responder(200, ['ok' => true]);
} catch (Throwable $error) {
    error_log((string) $error);
    ApiMiddleware::error(500, 'No se pudo sincronizar el pago.');
}
