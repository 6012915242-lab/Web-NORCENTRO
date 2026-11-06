<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Cliente;
use App\Nucleo\ModeloBaseORM;

/** Guarda y consulta la información de los clientes en MySQL. */
final class ClienteDAO extends ModeloBaseORM
{
    protected const TABLA = 'clientes';

    protected const ENTIDAD = Cliente::class;

    public function listar(string $buscar = ''): array
    {
        $consultaSQL = 'SELECT * FROM clientes WHERE estado="Activo"';
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (dni LIKE :term1 OR nombres LIKE :term2 OR apellidos LIKE :term3)';
            $terminoBusqueda = '%'.$buscar.'%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY apellidos,nombres';

        return $this->obtenerTodos($consultaSQL, $parametros);
    }

    public function buscarActivos(string $buscar = '', int $limite = 20): array
    {
        $limite = max(1, min(50, $limite));
        $consultaSQL = 'SELECT id,dni,nombres,apellidos,telefono,correo FROM clientes WHERE estado="Activo"';
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (
                dni LIKE :term1 OR CONCAT(nombres, " ", apellidos) LIKE :term2
                OR CONCAT(apellidos, " ", nombres) LIKE :term3
                OR nombres LIKE :term4 OR apellidos LIKE :term5
            )';
            $terminoBusqueda = '%'.$buscar.'%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda,
                'term4' => $terminoBusqueda,
                'term5' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY apellidos,nombres LIMIT '.$limite;

        return $this->obtenerTodos($consultaSQL, $parametros);
    }

    public function obtenerActivo(int $id): ?array
    {
        return $this->obtenerUno(
            'SELECT id, dni, nombres, apellidos, telefono, correo
             FROM clientes
             WHERE id = :id AND estado = "Activo"',
            ['id' => $id]
        );
    }

    public function obtener(int $id): ?array
    {
        return $this->obtenerUno(
            'SELECT * FROM clientes WHERE id = :id',
            ['id' => $id]
        );
    }

    /** Recupera un cliente como entidad para aplicar reglas de negocio tipadas. */
    public function obtenerEntidad(int $id): ?Cliente
    {
        $cliente = $this->buscarPorId($id);

        return $cliente instanceof Cliente ? $cliente : null;
    }

    public function existeDni(string $dni, ?int $excepto = null): bool
    {
        $consultaSql = 'SELECT COUNT(*) FROM clientes WHERE dni = :dni';
        $parametros = ['dni' => $dni];

        if ($excepto !== null) {
            $consultaSql .= ' AND id <> :id';
            $parametros['id'] = $excepto;
        }

        return (int) $this->preparar($consultaSql, $parametros)->fetchColumn() > 0;
    }

    public function guardar(array $datos): int
    {
        return $this->insertar($this->columnasEditables($datos));
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->actualizarPorId($id, $this->columnasEditables($datos));
    }

    public function desactivar(int $id): bool
    {
        $filasActualizadas = $this->ejecutar(
            'UPDATE clientes SET estado = :nuevo_estado WHERE id = :id AND estado = :estado_actual',
            ['nuevo_estado' => 'Inactivo', 'estado_actual' => 'Activo', 'id' => $id]
        );

        return $filasActualizadas === 1;
    }

    /** Solo se permiten las columnas que el formulario puede modificar. */
    private function columnasEditables(array $datos): array
    {
        $columnas = ['dni', 'nombres', 'apellidos', 'telefono', 'direccion', 'correo'];

        return array_intersect_key($datos, array_flip($columnas)) + array_fill_keys($columnas, null);
    }
}
