<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Servicios\ConsultaDniServicio;
use DomainException;
use RuntimeException;
use Throwable;

/** Responde al formulario de clientes sin mostrar datos internos del servidor. */
final class ConsultaDniControlador
{
    public function consultar(array $datos): never
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        try {
            $resultado = (new ConsultaDniServicio())->consultar((string) ($datos['dni'] ?? ''));
            echo json_encode(['ok' => true, 'datos' => $resultado], JSON_UNESCAPED_UNICODE);
        } catch (DomainException $error) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'mensaje' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (RuntimeException $error) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'mensaje' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $error) {
            error_log('Error al consultar DNI: ' . get_class($error));
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo completar la consulta.']);
        }
        exit;
    }
}
