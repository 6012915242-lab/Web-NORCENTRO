<?php

declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\OfertaDAO;
use DomainException;

final class GestionOfertasServicio
{
    public function desactivarOferta(OfertaDAO $ofertaDAO, int $id): void
    {
        if ($id < 1 || ! $ofertaDAO->desactivar($id)) {
            throw new DomainException('No se encontró una oferta disponible para desactivar.');
        }
    }

    public function validarYGuardarOferta(OfertaDAO $ofertaDAO, array $entrada): int
    {
        return $ofertaDAO->guardar($this->prepararOferta($ofertaDAO, $entrada));
    }

    public function validarYActualizarOferta(OfertaDAO $ofertaDAO, int $id, array $entrada): void
    {
        if ($id < 1) {
            throw new DomainException('No se encontró una oferta válida.');
        }

        $ofertaDAO->actualizar($id, $this->prepararOferta($ofertaDAO, $entrada));
    }

    public function prepararOferta(OfertaDAO $ofertaDAO, array $entrada): array
    {
        $idMoto = filter_var($entrada['moto_id'] ?? null, FILTER_VALIDATE_INT);
        $titulo = TextoEntrada::normalizar($entrada['titulo'] ?? '');
        $descripcion = TextoEntrada::normalizar($entrada['descripcion'] ?? '');
        $normal = filter_var($entrada['precio_normal'] ?? null, FILTER_VALIDATE_FLOAT);
        $oferta = filter_var($entrada['precio_oferta'] ?? null, FILTER_VALIDATE_FLOAT);
        $inicio = TextoEntrada::normalizar($entrada['fecha_inicio'] ?? '');
        $fin = TextoEntrada::normalizar($entrada['fecha_fin'] ?? '');

        if (! $idMoto || $idMoto < 1 || mb_strlen($titulo) < 3 || mb_strlen($titulo) > 100 || mb_strlen($descripcion) > 255) {
            throw new DomainException('Revisa el modelo seleccionado, el título y la descripción de la oferta.');
        }

        $normalTexto = $entrada['precio_normal'] ?? null;
        $ofertaTexto = $entrada['precio_oferta'] ?? null;
        if (
            $normal === false || $oferta === false || $normal <= 0 || $oferta <= 0 || $oferta >= $normal
            || ! is_scalar($normalTexto) || ! is_scalar($ofertaTexto)
            || ! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', trim((string) $normalTexto))
            || ! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', trim((string) $ofertaTexto))
        ) {
            throw new DomainException('Ingresa precios válidos de hasta dos decimales; el promocional debe ser menor al normal.');
        }

        if (! $this->esFechaValida($inicio) || ! $this->esFechaValida($fin) || $fin < $inicio) {
            throw new DomainException('Revisa las fechas de inicio y fin de la oferta.');
        }

        if (! $ofertaDAO->motoActiva((int) $idMoto)) {
            throw new DomainException('La oferta debe estar asociada a una motocicleta activa.');
        }

        return [
            'moto' => (int) $idMoto,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'normal' => (float) $normal,
            'oferta' => (float) $oferta,
            'inicio' => $inicio,
            'fin' => $fin,
        ];
    }

    private function esFechaValida(string $valor): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return false;
        }

        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }
}
