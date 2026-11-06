<?php

declare(strict_types=1);

namespace App\Servicios;

/** Prepara las opciones y aplica los filtros del catálogo. */
final class CatalogoFiltrosServicio
{
    public function preparar(array $motos, array $consulta, bool $mostrarCatalogo): array
    {
        $marcaSeleccionada = $this->entero($consulta['marca'] ?? 0);
        $cilindradaSeleccionada = $this->entero($consulta['cilindrada'] ?? 0);
        $modeloEntrada = $consulta['modelo'] ?? '';
        $modeloSeleccionado = is_string($modeloEntrada) ? trim($modeloEntrada) : '';
        $precioEntrada = $consulta['precio_max'] ?? 0;
        $precioMaximo = is_scalar($precioEntrada) ? (float) $precioEntrada : 0.0;
        $marcasCatalogo = [];
        $modelosCatalogo = [];
        $cilindradasCatalogo = [];
        $motoDestacada = null;

        /** Reúne las opciones disponibles y localiza la moto destacada. */
        foreach ($motos as $moto) {
            $marcasCatalogo[(int) $moto['marca_id']] = $moto['marca'];
            if ($marcaSeleccionada === 0 || (int) $moto['marca_id'] === $marcaSeleccionada) {
                $modelosCatalogo[$moto['modelo']] = $moto['modelo'];
                $cilindradasCatalogo[(int) $moto['cilindrada']] = (int) $moto['cilindrada'];
            }
            if ($motoDestacada === null && $moto['modelo'] === 'Pulsar NS400Z') {
                $motoDestacada = $moto;
            }
        }

        /** Descarta un modelo que no pertenece a las opciones disponibles. */
        if ($modeloSeleccionado !== '' && ! isset($modelosCatalogo[$modeloSeleccionado])) {
            $modeloSeleccionado = '';
        }

        asort($marcasCatalogo);
        ksort($modelosCatalogo);
        sort($cilindradasCatalogo);

        /** En el catálogo se aplican las opciones seleccionadas; en inicio no se filtra. */
        $catalogo = $mostrarCatalogo ? array_values(array_filter(
            $motos,
            static function (array $moto) use (
                $marcaSeleccionada,
                $modeloSeleccionado,
                $cilindradaSeleccionada,
                $precioMaximo
            ): bool {
                return ($marcaSeleccionada === 0 || (int) $moto['marca_id'] === $marcaSeleccionada)
                    && ($modeloSeleccionado === '' || $moto['modelo'] === $modeloSeleccionado)
                    && ($cilindradaSeleccionada === 0 || (int) $moto['cilindrada'] === $cilindradaSeleccionada)
                    && ($precioMaximo <= 0 || (float) $moto['precio'] <= $precioMaximo);
            }
        )) : [];

        return compact(
            'marcaSeleccionada',
            'modeloSeleccionado',
            'cilindradaSeleccionada',
            'precioMaximo',
            'marcasCatalogo',
            'modelosCatalogo',
            'cilindradasCatalogo',
            'motoDestacada',
            'catalogo'
        );
    }

    private function entero(mixed $valor): int
    {
        $numero = filter_var($valor, FILTER_VALIDATE_INT);

        return $numero === false || $numero < 0 ? 0 : $numero;
    }
}
