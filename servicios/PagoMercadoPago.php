<?php
declare(strict_types = 1);
namespace App\Servicios;
/** Encapsula las llamadas a la API de Mercado Pago para el checkout. */
final class PagoMercadoPago
{
    private string $tokenAcceso;

    public function __construct(?string $tokenAcceso = null)
    {
        $this->tokenAcceso = trim($tokenAcceso ?? (getenv('MP_ACCESS_TOKEN')?:''));
        if ($this->tokenAcceso === '') throw new \RuntimeException('Falta configurar MP_ACCESS_TOKEN en el servidor.');
        if (!function_exists('curl_init')) throw new \RuntimeException('PHP necesita la extensión cURL para conectar con Mercado Pago.');
    }

    public function crearOrden(array $pedido): array
    {
        $elementos = [];
        foreach ($pedido['items'] as $linea) {
            $elementos[] = ['title' => $linea['name'] . ' · ' . $linea['color'],
                'quantity' => (int)$linea['cantidad'],
                'unit_measure' => 'unit',
                'unit_price' => number_format((float)$linea['precio'],
                    2,
                    '.',
                    ''),
                'total_amount' => number_format((float)$linea['subtotal'],
                    2,
                    '.',
                    '')];
        }
        $body = ['type' => 'online',
            'processing_mode' => 'manual',
            'total_amount' => number_format((float)$pedido['total'],
                2,
                '.',
                ''),
            'external_reference' => 'NORCENTRO-' . $pedido['id'],
            'description' => 'Compra NORCENTRO #' . $pedido['id'],
            'expiration_time' => 'PT20M',
            'payer' => ['email' => $pedido['correo']],
            'items' => $elementos];
        $base = trim(getenv('NORCENTRO_PUBLIC_URL')?:'');
        if ($base !== '' && str_starts_with($base, 'https://')) {
            $retorno = rtrim($base, '/') . '/index.php?page=compra&request_id=' . rawurlencode($pedido['request_id']);
            $body['config'] = ['online' => ['success_url' => $retorno . '&resultado=aprobado',
                    'failure_url' => $retorno . '&resultado=fallido',
                    'pending_url' => $retorno . '&resultado=pendiente',
                    'auto_return' => 'approved']];
        }
        $resultado = $this->enviarSolicitud('POST', 'https://api.mercadopago.com/v1/orders', $body, $pedido['request_id']);
        $checkout = (string)($resultado['checkout_url'] ?? '');
        $parts = parse_url($checkout);
        $host = strtolower((string)($parts['host'] ?? ''));
        if (($parts['scheme'] ?? '') !== 'https' || !in_array($host, ['www.mercadopago.com.pe',
                'mercadopago.com.pe',
                'www.mercadopago.com',
                'mercadopago.com'], true)) throw new \RuntimeException('Mercado Pago no devolvió una URL de checkout válida.');
        return ['mp_order_id' => (string)($resultado['id'] ?? ''),
            'checkout_url' => $checkout,
            'response' => $resultado];
    }

    public function obtenerOrden(string $idPedido): array
    {
        if (!preg_match('/^[A-Za-z0-9_-]{8,80}$/', $idPedido)) throw new \InvalidArgumentException('Identificador de pago no válido.');
        return $this->enviarSolicitud('GET', 'https://api.mercadopago.com/v1/orders/' . rawurlencode($idPedido));
    }

    private function enviarSolicitud(string $metodo, string $url, ?array $body = null, ?string $idempotency = null): array
    {
        $encabezados = ['Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->tokenAcceso];
        if ($idempotency !== null)$encabezados[] = 'X-Idempotency-Key: ' . $idempotency;
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $metodo,
                CURLOPT_HTTPHEADER => $encabezados,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2]);
        if ($body !== null)curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
        $valorOriginal = curl_exec($curl);
        $estado = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($valorOriginal === false)throw new \RuntimeException('No se pudo conectar con Mercado Pago: ' . $error);
        $datosDecodificados = json_decode($valorOriginal, true);
        if ($estado < 200 || $estado >= 300 || !is_array($datosDecodificados)) {
            $motivo = is_array($datosDecodificados)?(string)($datosDecodificados['message'] ?? $datosDecodificados['error'] ?? 'respuesta no válida'):'respuesta no válida';
            throw new \RuntimeException('Mercado Pago respondió con error (HTTP ' . $estado . '): ' . $motivo);
        }
        return $datosDecodificados;
    }
}
