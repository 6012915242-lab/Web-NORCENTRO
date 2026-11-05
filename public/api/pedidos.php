<?php

declare(strict_types=1);
// Endpoint JSON: valida el carrito y solicita la creación de un pedido.
$rutaSesion = dirname(__DIR__, 2).'/storage/sessions';
if (! is_dir($rutaSesion)) {
    @mkdir($rutaSesion, 0770, true);
}
if (is_dir($rutaSesion) && is_writable($rutaSesion)) {
    session_save_path($rutaSesion);
}
session_set_cookie_params(['httponly' => true,
    'secure' => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax']);
session_start();
require_once dirname(__DIR__, 2).'/autoload.php';
use App\AccesoDatos\PedidoDAO;
use App\Configuracion\BaseDatos;
use App\Servicios\PagoMercadoPago;

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
        $idSolicitud = (string) ($_GET['request_id'] ?? '');
        if (! preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)) {
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
            $remote = (new PagoMercadoPago)->obtenerOrden((string) $pedido['mp_order_id']);
            if (($remote['external_reference'] ?? '') === 'NORCENTRO-'.$pedido['id'] && abs((float) ($remote['total_amount'] ?? 0) - (float) $pedido['total']) < 0.01) {
                $estado = (string) ($remote['status'] ?? '');
                $montoPagado = (float) ($remote['total_paid_amount'] ?? 0);
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
    $csrf = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $datosRecibidos['csrf'] ?? '');
    if (empty($_SESSION['_csrf']) || ! hash_equals((string) $_SESSION['_csrf'], $csrf)) {
        responder(403, ['ok' => false,
            'message' => 'La sesión venció. Recarga la página e inténtalo otra vez.']);
    }
    $idSolicitud = (string) ($datosRecibidos['request_id'] ?? '');
    if (! preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)) {
        responder(422, ['ok' => false,
            'message' => 'No se pudo identificar el intento de compra.']);
    }
    $cliente = $datosRecibidos['cliente'] ?? [];
    $pago = $datosRecibidos['pago'] ?? [];
    $elementos = $datosRecibidos['items'] ?? [];
    $nombre = trim((string) ($cliente['nombres'] ?? ''));
    $last = trim((string) ($cliente['apellidos'] ?? ''));
    $dni = preg_replace('/\D/', '', (string) ($cliente['dni'] ?? ''));
    $telefono = trim((string) ($cliente['telefono'] ?? ''));
    $correo = trim((string) ($cliente['correo'] ?? ''));
    $modalidad = (string) ($pago['modalidad'] ?? '');
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100 || mb_strlen($last) < 2 || mb_strlen($last) > 100) {
        responder(422, ['ok' => false,
            'message' => 'Escribe tus nombres y apellidos.']);
    }
    if (! preg_match('/^\d{8}$/', $dni)) {
        responder(422, ['ok' => false,
            'message' => 'El DNI debe tener 8 dígitos.']);
    }
    if (! preg_match('/^[+0-9 ()-]{7,20}$/', $telefono)) {
        responder(422, ['ok' => false,
            'message' => 'Ingresa un celular válido.']);
    }
    if (! filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
        responder(422, ['ok' => false,
            'message' => 'Ingresa un correo válido.']);
    }
    if (! in_array($modalidad, ['Contado',
        'Crédito'], true)) {
        responder(422, ['ok' => false,
            'message' => 'Selecciona una forma de pago válida.']);
    }
    if (! is_array($elementos) || count($elementos) < 1 || count($elementos) > 12) {
        responder(422, ['ok' => false,
            'message' => 'El carrito debe tener entre 1 y 12 modelos.']);
    }
    $articulosValidos = [];
    $seen = [];
    foreach ($elementos as $elemento) {
        if (! is_array($elemento)) {
            responder(422, ['ok' => false,
                'message' => 'Hay un producto inválido en el carrito.']);
        }
        $moto = (int) ($elemento['moto_id'] ?? 0);
        $color = (int) ($elemento['color_id'] ?? 0);
        $qty = (int) ($elemento['cantidad'] ?? 0);
        $key = $moto.':'.$color;
        if ($moto < 1 || $qty < 1 || $qty > 20 || isset($seen[$key])) {
            responder(422, ['ok' => false,
                'message' => 'Hay productos duplicados o cantidades inválidas.']);
        }
        $seen[$key] = true;
        $articulosValidos[] = ['moto_id' => $moto,
            'color_id' => $color > 0 ? $color : null,
            'cantidad' => $qty];
    }
    $inicial = (float) ($pago['inicial'] ?? 0);
    $cuotas = (int) ($pago['cuotas'] ?? 0);
    $creditConsent = ($datosRecibidos['acepta_credito'] ?? false) === true || ($datosRecibidos['acepta_credito'] ?? null) === 1 || ($datosRecibidos['acepta_credito'] ?? null) === '1';
    if ($modalidad === 'Crédito' && ! $creditConsent) {
        responder(422, ['ok' => false,
            'message' => 'Autoriza el uso de tus datos para gestionar la solicitud de crédito.']);
    }
    if ($modalidad === 'Contado' && trim(getenv('MP_ACCESS_TOKEN') ?: '') === '') {
        responder(503, ['ok' => false,
            'message' => 'Mercado Pago aún no está configurado. El administrador debe cargar MP_ACCESS_TOKEN en el servidor antes de cobrar.']);
    }
    $pedido = $pedidos->crear(['request_id' => $idSolicitud,
        'nombres' => $nombre,
        'apellidos' => $last,
        'dni' => $dni,
        'telefono' => $telefono,
        'correo' => $correo,
        'modalidad' => $modalidad,
        'inicial' => $inicial,
        'cuotas' => $cuotas,
        'acepta_credito' => $creditConsent,
        'items' => $articulosValidos]);
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
    $mp = (new PagoMercadoPago)->crearOrden(['id' => $pedido['id'],
        'request_id' => $idSolicitud,
        'correo' => $correo,
        'total' => $pedido['total'],
        'items' => $pedido['items']]);
    $pedidos->guardarMercadoPago((int) $pedido['id'], $mp['mp_order_id'], $mp['checkout_url']);
    responder(201, ['ok' => true,
        'pedido_id' => $pedido['id'],
        'modalidad' => 'Contado',
        'estado' => 'Pendiente de pago',
        'checkout_url' => $mp['checkout_url']]);
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
