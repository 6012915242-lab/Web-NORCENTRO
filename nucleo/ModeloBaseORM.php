<?php
declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;

/** Base común para los DAO: centraliza PDO y el uso de consultas preparadas. */
abstract class ModeloBaseORM
{
    public function __construct(protected PDO $conexion)
    {
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
     * @param class-string<T> $claseEntidad
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

        if (!is_subclass_of($claseEntidad, EntidadORM::class)) {
            throw new RuntimeException('La clase indicada no implementa EntidadORM.');
        }

        return $claseEntidad::desdeRegistro($registro);
    }

    /**
     * @template T of EntidadORM
     * @param class-string<T> $claseEntidad
     * @return list<T>
     */
    protected function hidratarEntidades(
        string $claseEntidad,
        string $consultaSql,
        array $parametros = []
    ): array {
        if (!is_subclass_of($claseEntidad, EntidadORM::class)) {
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
