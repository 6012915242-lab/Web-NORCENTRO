<?php

declare(strict_types=1);

namespace App\Servicios;

use InvalidArgumentException;

/** Valida los datos del carrito antes de crear un pedido. */
final class ValidacionPedidoServicio
{
    public function validar(array $entrada): array
    {
        $idSolicitud = $this->texto($entrada['request_id'] ?? '');
        if (! preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)) {
            throw new InvalidArgumentException('No se pudo identificar el intento de compra.');
        }

        $cliente = $entrada['cliente'] ?? [];
        $pago = $entrada['pago'] ?? [];
        $elementos = $entrada['items'] ?? [];
        if (! is_array($cliente) || ! is_array($pago)) {
            throw new InvalidArgumentException('Revisa los datos del cliente y del pago.');
        }

        $nombres = $this->texto($cliente['nombres'] ?? '');
        $apellidos = $this->texto($cliente['apellidos'] ?? '');
        $dni = $this->texto($cliente['dni'] ?? '');
        $telefono = $this->texto($cliente['telefono'] ?? '');
        $correo = $this->texto($cliente['correo'] ?? '');
        $modalidad = $this->texto($pago['modalidad'] ?? '');

        if (mb_strlen($nombres) < 2 || mb_strlen($nombres) > 100 || mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 100) {
            throw new InvalidArgumentException('Escribe tus nombres y apellidos.');
        }
        try {
            Validador::dni($dni, 'El DNI debe tener 8 dígitos.');
            Validador::telefonoFlexible($telefono, 'Ingresa un celular válido.');
            Validador::correo($correo, 'Ingresa un correo válido.');
        } catch (\DomainException $error) {
            throw new InvalidArgumentException($error->getMessage(), 0, $error);
        }
        if (strlen($correo) > 120) {
            throw new InvalidArgumentException('Ingresa un correo válido.');
        }
        if (! in_array($modalidad, ['Contado', 'Crédito'], true)) {
            throw new InvalidArgumentException('Selecciona una forma de pago válida.');
        }

        $articulos = $this->validarArticulos($elementos);
        $aceptaCredito = in_array($entrada['acepta_credito'] ?? null, [true, 1, '1'], true);
        if ($modalidad === 'Crédito' && ! $aceptaCredito) {
            throw new InvalidArgumentException('Autoriza el uso de tus datos para gestionar la solicitud de crédito.');
        }

        $inicial = $this->texto($pago['inicial'] ?? '0');
        $numeroCuotas = filter_var($pago['cuotas'] ?? 0, FILTER_VALIDATE_INT);
        if ($modalidad === 'Crédito' && (
            ! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $inicial)
            || $numeroCuotas === false
            || $numeroCuotas < 0
            || $numeroCuotas > 36
        )) {
            throw new InvalidArgumentException('Revisa la inicial y el número de cuotas.');
        }

        return [
            'request_id' => $idSolicitud,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'dni' => $dni,
            'telefono' => $telefono,
            'correo' => $correo,
            'modalidad' => $modalidad,
            'inicial' => $modalidad === 'Crédito' ? (float) $inicial : 0.0,
            'cuotas' => $modalidad === 'Crédito' ? (int) $numeroCuotas : 0,
            'acepta_credito' => $aceptaCredito,
            'items' => $articulos,
        ];
    }

    private function validarArticulos(mixed $elementos): array
    {
        if (! is_array($elementos) || count($elementos) < 1 || count($elementos) > 12) {
            throw new InvalidArgumentException('El carrito debe tener entre 1 y 12 modelos.');
        }

        $articulos = [];
        $vistos = [];
        foreach ($elementos as $elemento) {
            if (! is_array($elemento)) {
                throw new InvalidArgumentException('Hay un producto inválido en el carrito.');
            }

            $motoId = filter_var($elemento['moto_id'] ?? null, FILTER_VALIDATE_INT);
            $cantidad = filter_var($elemento['cantidad'] ?? null, FILTER_VALIDATE_INT);
            $colorEntrada = $elemento['color_id'] ?? null;
            $colorId = $colorEntrada === null ? null : filter_var($colorEntrada, FILTER_VALIDATE_INT);
            if (! $motoId || $motoId < 1 || ! $cantidad || $cantidad < 1 || $cantidad > 20 || $colorId === false || ($colorId !== null && $colorId < 1)) {
                throw new InvalidArgumentException('Hay productos o cantidades inválidas.');
            }

            $clave = $motoId.':'.($colorId ?? 0);
            if (isset($vistos[$clave])) {
                throw new InvalidArgumentException('Hay productos duplicados en el carrito.');
            }

            $vistos[$clave] = true;
            $articulos[] = [
                'moto_id' => $motoId,
                'color_id' => $colorId,
                'cantidad' => $cantidad,
            ];
        }

        return $articulos;
    }

    private function texto(mixed $valor): string
    {
        if (! is_string($valor) && ! is_int($valor) && ! is_float($valor)) {
            return '';
        }

        return trim((string) $valor);
    }
}
