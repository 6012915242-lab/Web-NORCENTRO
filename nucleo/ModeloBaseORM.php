<?php

declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;

/**
 * ORM artesanal: clase base de todos los DAO.
 *
 * Responsabilidades:
 *  - Ejecutar consultas preparadas con PDO (protección contra inyección SQL).
 *  - Mapear registros de una tabla a entidades (Tabla -> Objeto).
 *  - Ofrecer operaciones genéricas: buscar, contar, insertar y actualizar.
 *  - Resolver relaciones N:1 (perteneceA) y 1:N (tieneMuchos).
 *  - Agrupar varias operaciones en una transacción ACID.
 *
 * Cada DAO hijo solo declara su TABLA y su ENTIDAD; las consultas complejas
 * (JOIN, agregados) se escriben en el propio DAO usando los métodos protegidos.
 */
abstract class ModeloBaseORM
{
    /** Tabla que mapea el DAO. */
    protected const TABLA = '';

    /** Clave primaria de la tabla. */
    protected const CLAVE_PRIMARIA = 'id';

    /** Clase de entidad (implementa EntidadORM) en la que se convierten los registros. */
    protected const ENTIDAD = '';

    public function __construct(protected PDO $conexion) {}

    // ------------------------------------------------------------------
    // Operaciones genéricas de lectura
    // ------------------------------------------------------------------

    /** Busca un registro por su clave primaria y lo devuelve como entidad. */
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
     * Devuelve las entidades que cumplen condiciones de igualdad.
     *
     * @param  array<string, mixed>  $condiciones  columna => valor
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

    /** Cuenta los registros que cumplen condiciones de igualdad (columna => valor). */
    public function contarPor(array $condiciones = []): int
    {
        [$where, $parametros] = $this->construirCondiciones($condiciones);

        return (int) $this->preparar(
            'SELECT COUNT(*) FROM '.$this->tabla().$where,
            $parametros
        )->fetchColumn();
    }

    // ------------------------------------------------------------------
    // Operaciones genéricas de escritura
    // ------------------------------------------------------------------

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

    /** Actualiza las columnas indicadas del registro y devuelve las filas afectadas. */
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

    // ------------------------------------------------------------------
    // Relaciones entre entidades
    // ------------------------------------------------------------------

    /**
     * Relación N:1. Ejemplo: una Moto pertenece a una Marca.
     *
     * @param  class-string<ModeloBaseORM>  $claseDao  DAO de la entidad relacionada
     */
    protected function perteneceA(string $claseDao, int $idForaneo): ?EntidadORM
    {
        if (! is_subclass_of($claseDao, self::class)) {
            throw new RuntimeException('La clase indicada no es un DAO válido.');
        }

        return (new $claseDao($this->conexion))->buscarPorId($idForaneo);
    }

    /**
     * Relación 1:N. Ejemplo: un Crédito tiene muchas Cuotas.
     *
     * @param  class-string<EntidadORM>  $claseEntidad
     * @return list<EntidadORM>
     */
    protected function tieneMuchos(string $claseEntidad, string $tabla, string $columnaForanea, int $id): array
    {
        return $this->hidratarEntidades(
            $claseEntidad,
            sprintf(
                'SELECT * FROM %s WHERE %s = :id',
                $this->identificador($tabla),
                $this->identificador($columnaForanea)
            ),
            ['id' => $id]
        );
    }

    // ------------------------------------------------------------------
    // Ejecución de consultas (usadas por los DAO hijos)
    // ------------------------------------------------------------------

    /** Prepara y ejecuta una consulta con parámetros enlazados. */
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

    /** Ejecuta INSERT, UPDATE o DELETE y devuelve las filas afectadas. */
    protected function ejecutar(string $consultaSql, array $parametros = []): int
    {
        return $this->preparar($consultaSql, $parametros)->rowCount();
    }

    protected function ultimoIdInsertado(): int
    {
        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Convierte el primer registro de la consulta en una entidad.
     *
     * @template T of EntidadORM
     *
     * @param  class-string<T>  $claseEntidad
     * @return T|null
     */
    protected function hidratarEntidad(string $claseEntidad, string $consultaSql, array $parametros = []): ?EntidadORM
    {
        $this->validarClaseEntidad($claseEntidad);
        $registro = $this->obtenerUno($consultaSql, $parametros);

        return $registro === null ? null : $claseEntidad::desdeRegistro($registro);
    }

    /**
     * Convierte todos los registros de la consulta en entidades.
     *
     * @template T of EntidadORM
     *
     * @param  class-string<T>  $claseEntidad
     * @return list<T>
     */
    protected function hidratarEntidades(string $claseEntidad, string $consultaSql, array $parametros = []): array
    {
        $this->validarClaseEntidad($claseEntidad);

        return array_map(
            static fn (array $registro): EntidadORM => $claseEntidad::desdeRegistro($registro),
            $this->obtenerTodos($consultaSql, $parametros)
        );
    }

    /**
     * Ejecuta la operación dentro de una transacción: confirma si todo sale bien
     * y deshace todos los cambios si ocurre cualquier error (atomicidad).
     */
    protected function ejecutarEnTransaccion(callable $operacion): mixed
    {
        // Si ya existe una transacción abierta, la operación forma parte de ella.
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

    // ------------------------------------------------------------------
    // Ayudantes internos
    // ------------------------------------------------------------------

    /** @return array{0: string, 1: array<string, mixed>} cláusula WHERE y sus parámetros */
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
        $this->validarClaseEntidad(static::ENTIDAD);

        return static::ENTIDAD;
    }

    private function validarClaseEntidad(string $claseEntidad): void
    {
        if (! is_subclass_of($claseEntidad, EntidadORM::class)) {
            throw new RuntimeException(static::class.': '.$claseEntidad.' no implementa EntidadORM.');
        }
    }

    /** Los nombres de tablas y columnas no pueden ir como parámetros PDO, por eso se validan aquí. */
    private function identificador(string $nombre): string
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $nombre)) {
            throw new RuntimeException('Nombre de columna o tabla no válido: '.$nombre);
        }

        return '`'.$nombre.'`';
    }
}
