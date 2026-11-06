<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Oferta;
use App\Nucleo\ModeloBaseORM;
use PDO;

/** Consulta y actualiza las promociones del catálogo. */
final class OfertaDAO extends ModeloBaseORM
{
    protected const TABLA = 'ofertas';
    protected const ENTIDAD = Oferta::class;

    public function activas(): array
    {
        $sql = "
            SELECT o.*, m.modelo, m.imagen, m.precio AS precio_catalogo,
                   m.cilindrada, m.anio, m.color, m.marca_id, ma.nombre AS marca,
                   (SELECT COUNT(*) FROM unidades_moto u
                    WHERE u.moto_id = m.id AND u.estado = 'Disponible') AS disponibles
            FROM ofertas o
            JOIN motos m ON m.id = o.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            WHERE o.estado = 'Activa' AND m.estado = 'Activo' AND ma.estado = 'Activo'
              AND CURDATE() BETWEEN o.fecha_inicio AND o.fecha_fin
            ORDER BY o.precio_oferta, o.fecha_fin, o.id
        ";

        return $this->conexion->query($sql)->fetchAll();
    }

    public function activaParaMoto(int $idMoto): ?array
    {
        $consulta = $this->conexion->prepare("
            SELECT o.* FROM ofertas o
            WHERE o.moto_id = :moto AND o.estado = 'Activa'
              AND CURDATE() BETWEEN o.fecha_inicio AND o.fecha_fin
            ORDER BY o.precio_oferta, o.fecha_fin, o.id LIMIT 1
        ");
        $consulta->execute(['moto' => $idMoto]);

        return $consulta->fetch() ?: null;
    }

    public function listar(): array
    {
        $sql = '
            SELECT o.*, m.modelo, m.imagen, ma.nombre AS marca
            FROM ofertas o
            JOIN motos m ON m.id = o.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            ORDER BY o.fecha_fin DESC
        ';

        return $this->conexion->query($sql)->fetchAll();
    }

    public function motoActiva(int $idMoto): bool
    {
        $consulta = $this->conexion->prepare("
            SELECT 1 FROM motos m
            JOIN marcas ma ON ma.id = m.marca_id
            WHERE m.id = :id AND m.estado = 'Activo' AND ma.estado = 'Activo'
        ");
        $consulta->bindValue(':id', $idMoto, PDO::PARAM_INT);
        $consulta->execute();

        return (bool) $consulta->fetchColumn();
    }

    public function guardar(array $datos): int
    {
        return $this->insertar($this->columnasEditables($datos) + ['estado' => 'Activa']);
    }

    private function columnasEditables(array $datos): array
    {
        $mapa = ['moto' => 'moto_id', 'titulo' => 'titulo', 'descripcion' => 'descripcion',
            'normal' => 'precio_normal', 'oferta' => 'precio_oferta',
            'inicio' => 'fecha_inicio', 'fin' => 'fecha_fin'];
        $resultado = [];
        foreach ($mapa as $entrada => $columna) {
            if (array_key_exists($entrada, $datos)) {
                $resultado[$columna] = $datos[$entrada];
            }
        }

        return $resultado;
    }

    public function actualizar(int $id, array $datos): void
    {
        $datos['id'] = $id;
        $this->ejecutar(
            'UPDATE ofertas
             SET moto_id = :moto, titulo = :titulo, descripcion = :descripcion,
                 precio_normal = :normal, precio_oferta = :oferta,
                 fecha_inicio = :inicio, fecha_fin = :fin
             WHERE id = :id AND estado = :estado',
            $datos + ['estado' => 'Activa']
        );
    }

    public function actualizarEstadoFechas(): void
    {
        $this->conexion->exec("
            UPDATE ofertas SET estado = 'Vencida'
            WHERE estado = 'Activa' AND fecha_fin < CURDATE()
        ");
    }

    public function desactivar(int $id): bool
    {
        $filasActualizadas = $this->ejecutar(
            'UPDATE ofertas SET estado = :nuevo_estado WHERE id = :id AND estado <> :estado_anterior',
            [
                'nuevo_estado' => 'Inactiva',
                'estado_anterior' => 'Inactiva',
                'id' => $id
            ]
        );

        return $filasActualizadas === 1;
    }
}
