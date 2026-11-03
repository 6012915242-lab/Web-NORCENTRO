<?php
declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use PDOStatement;

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
}
