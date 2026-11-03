<?php
declare(strict_types = 1);
namespace App\AccesoDatos;

use App\Modelos\Cliente;
use App\Nucleo\ModeloBaseORM;
/** Guarda y consulta la información de los clientes en MySQL. */
final class ClienteDAO extends ModeloBaseORM
{
    public function listar(string $buscar = ''): array
    {
        $consultaSQL = 'SELECT * FROM clientes WHERE estado="Activo"';
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (dni LIKE :term1 OR nombres LIKE :term2 OR apellidos LIKE :term3)';
            $terminoBusqueda = '%' . $buscar . '%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY apellidos,nombres';
        $consulta = $this->conexion->prepare($consultaSQL);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    public function buscarActivos(string $buscar = '', int $limite = 20): array
    {
        $limite = max(1, min(50, $limite));
        $consultaSQL = 'SELECT id,dni,nombres,apellidos,telefono,correo FROM clientes WHERE estado="Activo"';
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (dni LIKE :term1 OR CONCAT(nombres," ",apellidos) LIKE :term2 OR CONCAT(apellidos," ",nombres) LIKE :term3 OR nombres LIKE :term4 OR apellidos LIKE :term5)';
            $terminoBusqueda = '%' . $buscar . '%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda,
                'term4' => $terminoBusqueda,
                'term5' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY apellidos,nombres LIMIT ' . $limite;
        $consulta = $this->conexion->prepare($consultaSQL);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    public function obtenerActivo(int $id): ?array
    {
        $consulta = $this->conexion->prepare('SELECT id,dni,nombres,apellidos,telefono,correo FROM clientes WHERE id=:id AND estado="Activo"');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch()?:null;
    }

    public function obtener(int $id): ?array {
        $consulta = $this->conexion->prepare('SELECT * FROM clientes WHERE id=:id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetch()?:null;
    }

    /** Recupera un cliente como entidad para aplicar reglas de negocio tipadas. */
    public function obtenerEntidad(int $id): ?Cliente
    {
        return $this->hidratarEntidad(
            Cliente::class,
            'SELECT id, dni, nombres, apellidos, telefono, direccion, correo, estado
             FROM clientes
             WHERE id = :id',
            ['id' => $id]
        );
    }

    public function existeDni(string $dni, ?int $excepto = null): bool {
        $s = 'SELECT COUNT(*) FROM clientes WHERE dni=:dni';
        $p = ['dni' => $dni];
        if ($excepto) {
            $s .= ' AND id<>:id';
            $p['id'] = $excepto;
        }
        $consulta = $this->conexion->prepare($s);
        $consulta->execute($p);
        return (int)$consulta->fetchColumn() > 0;
    }

    public function guardar(array $datos): int {
        $consulta = $this->conexion->prepare('INSERT INTO clientes(dni,nombres,apellidos,telefono,direccion,correo) VALUES(:dni,:nombres,:apellidos,:telefono,:direccion,:correo)');
        $consulta->execute($datos);
        return (int)$this->conexion->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void {
        $datos['id'] = $id;
        $consulta = $this->conexion->prepare('UPDATE clientes SET dni=:dni,nombres=:nombres,apellidos=:apellidos,telefono=:telefono,direccion=:direccion,correo=:correo WHERE id=:id');
        $consulta->execute($datos);
    }

    public function desactivar(int $id): void {
        $consulta = $this->conexion->prepare('UPDATE clientes SET estado="Inactivo" WHERE id=:id');
        $consulta->execute(['id' => $id]);
    }
}
