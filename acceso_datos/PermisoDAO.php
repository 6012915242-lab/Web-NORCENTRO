<?php
declare(strict_types=1);

namespace App\AccesoDatos;

use App\Nucleo\ModeloBaseORM;

/** Consulta los permisos RBAC asociados al rol de cada usuario. */
final class PermisoDAO extends ModeloBaseORM
{
    public function codigosPorUsuario(int $usuarioId): array
    {
        $registros = $this->obtenerTodos(
            'SELECT permiso
             FROM vista_usuarios_permisos
             WHERE usuario_id = :usuario_id
             ORDER BY permiso',
            ['usuario_id' => $usuarioId]
        );

        return array_values(array_map(
            static fn (array $registro): string => (string) $registro['permiso'],
            $registros
        ));
    }

    public function usuarioTiene(int $usuarioId, string $codigoPermiso): bool
    {
        $registro = $this->obtenerUno(
            'SELECT 1
             FROM vista_usuarios_permisos
             WHERE usuario_id = :usuario_id
               AND permiso = :permiso
             LIMIT 1',
            [
                'usuario_id' => $usuarioId,
                'permiso' => $codigoPermiso,
            ]
        );

        return $registro !== null;
    }
}
