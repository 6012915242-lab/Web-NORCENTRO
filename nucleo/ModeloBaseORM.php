<?php

declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;

/**
 * ORM artesanal: base común para los DAO.
 * Centraliza PDO, consultas preparadas, transacciones y el mapeo genérico tabla -> entidad.
 */
abstract class ModeloBaseORM
{
    /** Tabla que mapea el DAO. Cada DAO hijo la define. */
    protected const TABLA = '';

    /** Clave primaria de la tabla. */
    protected const CLAVE_PRIMARIA = 'id';

    /** Clase de entidad (implementa EntidadORM) en la que se convierten los registros. */
    protected const ENTIDAD = '';

    public function __construct(protected PDO $conexion) {}

    /**
     * Busca un registro por su clave primaria y lo devuelve como entidad.
     * Mapeo Tabla -> Objeto: SELECT * FROM <TABLA> WHERE <CLAVE> = :id.
     */
    public function buscarPorId(int $id): ?EntidadORM
    {
        return $this->hidratarEntidad(
            $this->claseEntidad(),
            sprintf(
                'SELECT * FROM %s WHERE %s = :id',
                $this->tabla(),
                $this->identificador(static::CLAVE_PRIMARIA)
            ),
            ['id' => $id]
        );
    }

    /**
     * Devuelve las entidades que cumplen condiciones de igualdad (columna => valor).
     *
     * @return list<EntidadORM>
     */
    public function buscarEntidadesPor(array $condiciones = [], string $ordenarPor = ''): array
    {
        [$where, $parametros] = $this->construirCondiciones($condiciones);
        $consultaSql = 'SELECT * FROM '.$this->tabla().$where;
        if ($ordenarPor !== '') {
            $consultaSql .= ' ORDER BY '.$this->identificador($ordenarPor);
        }

        return $this->hidratarEntidades($this->claseEntidad(), $consultaSql, $parametros);
    }

    /** Cuenta los registros que cumplen condiciones de igualdad. */
    public function contarPor(array $condiciones = []): int
    {
        [$where, $parametros] = $this->construirCondiciones($condiciones);

        return (int) $this->preparar(
            'SELECT COUNT(*) FROM '.$this->tabla().$where,
            $parametros
        )->fetchColumn();
    }

    /** Inserta un registro (columna => valor) y devuelve el id generado. */
    protected function insertar(array $datos): int
    {
        if ($datos === []) {
            throw new RuntimeException('No hay datos para insertar.');
        }
        $columnas = array_map(fn (string $columna): string => $this->identificador($columna), array_keys($datos));
        $marcadores = array_map(static fn (string $columna): string => ':'.$columna, array_keys($datos));

        $this->ejecutar(
            sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->tabla(),
                implode(', ', $columnas),
                implode(', ', $marcadores)
            ),
            $datos
        );

        return $this->ultimoIdInsertado();
    }

    /** Actualiza las columnas indicadas del registro con la clave primaria dada. */
    protected function actualizarPorId(int $id, array $datos): int
    {
        if ($datos === []) {
            return 0;
        }
        $asignaciones = array_map(
            fn (string $columna): string => $this->identificador($columna).' = :'.$columna,
            array_keys($datos)
        );
        $datos['_id'] = $id;

        return $this->ejecutar(
            sprintf(
                'UPDATE %s SET %s WHERE %s = :_id',
                $this->tabla(),
                implode(', ', $asignaciones),
                $this->identificador(static::CLAVE_PRIMARIA)
            ),
            $datos
        );
    }

    /** @return array{0: string, 1: array} */
    private function construirCondiciones(array $condiciones): array
    {
        $partes = [];
        $parametros = [];
        foreach ($condiciones as $columna => $valor) {
            $marcador = 'c_'.$columna;
            $partes[] = $this->identificador((string) $columna).' = :'.$marcador;
            $parametros[$marcador] = $valor;
        }

        return [$partes === [] ? '' : ' WHERE '.implode(' AND ', $partes), $parametros];
    }

    private function tabla(): string
    {
        if (static::TABLA === '') {
            throw new RuntimeException(static::class.' no define la constante TABLA.');
        }

        return $this->identificador(static::TABLA);
    }

    /** @return class-string<EntidadORM> */
    private function claseEntidad(): string
    {
        if (static::ENTIDAD === '' || ! is_subclass_of(static::ENTIDAD, EntidadORM::class)) {
            throw new RuntimeException(static::class.' no define una ENTIDAD válida.');
        }

        return static::ENTIDAD;
    }

    /** Los nombres de tablas y columnas no pueden ir como parámetros PDO: se validan aquí. */
    private function identificador(string $nombre): string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $nombre)) {
            throw new RuntimeException('Nombre de columna o tabla no válido: '.$nombre);
        }

        return '`'.$nombre.'`';
    }

    protected function preparar(string $consultaSql, array $parametros = []): PDOStatement
    {
        $consulta = $this->conexion->prepare($consultaSql);
        $consulta->execute($parametros);

        return $consulta;
    }

    protected function obtenerTodos(string $consultaSql, array $parametros = []): array
    {
        return $this->preparar($consultaSql, $parametros)->fetchAll();
    }

    protected function obtenerUno(string $consultaSql, array $parametros = []): ?array
    {
        $registro = $this->preparar($consultaSql, $parametros)->fetch();

        return $registro === false ? null : $registro;
    }

    /**
     * @template T of EntidadORM
     *
     * @param  class-string<T>  $claseEntidad
     * @return T|null
     */
    protected function hidratarEntidad(
        string $claseEntidad,
        string $consultaSql,
        array $parametros = []
    ): ?EntidadORM {
        $registro = $this->obtenerUno($consultaSql, $parametros);

        if ($registro === null) {
            return null;
        }

        if (! is_subclass_of($claseEntidad, EntidadORM::class)) {
            throw new RuntimeException('La clase indicada no implementa EntidadORM.');
        }

        return $claseEntidad::desdeRegistro($registro);
    }

    /**
     * @template T of EntidadORM
     *
     * @param  class-string<T>  $claseEntidad
     * @return list<T>
     */
    protected function hidratarEntidades(
        string $claseEntidad,
        string $consultaSql,
        array $parametros = []
    ): array {
        if (! is_subclass_of($claseEntidad, EntidadORM::class)) {
            throw new RuntimeException('La clase indicada no implementa EntidadORM.');
        }

        return array_map(
            static fn (array $registro): EntidadORM => $claseEntidad::desdeRegistro($registro),
            $this->obtenerTodos($consultaSql, $parametros)
        );
    }

    protected function ejecutar(string $consultaSql, array $parametros = []): int
    {
        return $this->preparar($consultaSql, $parametros)->rowCount();
    }

    protected function ultimoIdInsertado(): int
    {
        return (int) $this->conexion->lastInsertId();
    }

    protected function ejecutarEnTransaccion(callable $operacion): mixed
    {
        if ($this->conexion->inTransaction()) {
            return $operacion();
        }

        $this->conexion->beginTransaction();

        try {
            $resultado = $operacion();
            $this->conexion->commit();

            return $resultado;
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $error;
        }
    }
}
