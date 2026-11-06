<?php

declare(strict_types=1);

namespace App\Controladores;

use App\Middlewares\ApiMiddleware;
use App\Servicios\ConsultaDniServicio;
use App\Servicios\TextoEntrada;
use DomainException;
use RuntimeException;
use Throwable;

/** Responde al formulario de clientes sin mostrar datos internos del servidor. */
final class ConsultaDniControlador
{
    public function consultar(array $datos): never
    {
        try {
            $dni = TextoEntrada::normalizar($datos['dni'] ?? '');
            $resultado = (new ConsultaDniServicio)->consultar($dni);
        } catch (DomainException $error) {
            // Dato mal ingresado por el usuario (por ejemplo, DNI incompleto).
            ApiMiddleware::error(422, $error->getMessage());
        } catch (RuntimeException $error) {
            // El proveedor no respondió o no está configurado.
            ApiMiddleware::error(503, $error->getMessage());
        } catch (Throwable $error) {
            error_log('Error al consultar DNI: '.get_class($error));
            ApiMiddleware::error(500, 'No se pudo completar la consulta.');
        }

        ApiMiddleware::responder(200, ['ok' => true, 'datos' => $resultado]);
    }
}
