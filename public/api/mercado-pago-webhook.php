<?php

declare(strict_types=1);
// Recibe avisos del proveedor de pago y actualiza el estado del pedido.
require_once dirname(__DIR__, 2).'/autoload.php';
use App\AccesoDatos\PedidoDAO;
use App\Configuracion\BaseDatos;
use App\Servicios\PagoMercadoPago;

header('Content-Type: application/json; charset=utf-8');
function responderWebhook(int $estado, array $body): never
{
    http_response_code($estado);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    $secret = trim(getenv('MP_WEBHOOK_SECRET') ?: '');
    if ($secret === '') {
        responderWebhook(503, ['ok' => false, 'message' => 'Falta configurar la firma secreta del webhook.']);
    }
    $idDato = strtolower((string) ($_GET['data.id'] ?? $_GET['data_id'] ?? ''));
    $idSolicitud = (string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? '');
    $signature = (string) ($_SERVER['HTTP_X_SIGNATURE'] ?? '');
    if ($idDato === '' || $idSolicitud === '' || $signature === '') {
        responderWebhook(401, ['ok' => false, 'message' => 'Notificación incompleta.']);
    }
    $parts = [];
    foreach (explode(',', $signature) as $entry) {
        $pair = explode('=', trim($entry), 2);
        if (count($pair) === 2) {
            $parts[$pair[0]] = $pair[1];
        }
    }
    if (empty($parts['ts']) || empty($parts['v1'])) {
        responderWebhook(401, ['ok' => false, 'message' => 'Firma inválida.']);
    }
    $manifest = 'id:'.$idDato.';request-id:'.$idSolicitud.';ts:'.$parts['ts'].';';
    $esperado = hash_hmac('sha256', $manifest, $secret);
    if (! hash_equals($esperado, $parts['v1'])) {
        responderWebhook(401, ['ok' => false, 'message' => 'Firma inválida.']);
    }
    $type = (string) ($_GET['type'] ?? '');
    if ($type !== '' && ! in_array($type, ['order', 'orders'], true)) {
        responderWebhook(200, ['ok' => true, 'ignored' => true]);
    }
    $remote = (new PagoMercadoPago)->obtenerOrden($idDato);
    $pedidoDAO = new PedidoDAO(BaseDatos::conectar());
    $pedido = $pedidoDAO->obtenerPorMercadoPago($idDato);
    if (
        ! $pedido
        || ($remote['external_reference'] ?? '') !== 'NORCENTRO-'.$pedido['id']
        || abs((float) ($remote['total_amount'] ?? 0) - (float) $pedido['total']) >= 0.01
    ) {
        responderWebhook(404, ['ok' => false, 'message' => 'La notificación no coincide con un pedido registrado.']);
    }
    $estado = (string) ($remote['status'] ?? '');
    $montoPagado = (float) ($remote['total_paid_amount'] ?? 0);
    if ($estado === 'processed' && $montoPagado + 0.009 >= (float) $pedido['total']) {
        $pedidoDAO->actualizarEstadoPago((int) $pedido['id'], $idDato, 'processed');
    } elseif (in_array($estado, ['canceled', 'expired'], true)) {
        $pedidoDAO->actualizarEstadoPago((int) $pedido['id'], $idDato, $estado);
    }
    responderWebhook(200, ['ok' => true]);
} catch (Throwable $e) {
    error_log((string) $e);
    responderWebhook(500, ['ok' => false,
        'message' => 'No se pudo sincronizar el pago.']);
}
