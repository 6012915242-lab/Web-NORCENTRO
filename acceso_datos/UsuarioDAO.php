<?php
declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Usuario;
use App\Nucleo\ModeloBaseORM;

/** Gestiona credenciales, bloqueos e intentos de acceso. */
final class UsuarioDAO extends ModeloBaseORM
{
    public function idRolPorNombre(string $nombreRol): ?int
    {
        $registro = $this->obtenerUno(
            'SELECT id FROM roles WHERE nombre = :nombre LIMIT 1',
            ['nombre' => $nombreRol]
        );

        return $registro === null ? null : (int) $registro['id'];
    }

    public function crear(
        int $rolId,
        string $nombreUsuario,
        string $passwordHash,
        string $nombre
    ): int {
        $this->ejecutar(
            'INSERT INTO usuarios (rol_id, usuario, password_hash, nombre)
             VALUES (:rol_id, :usuario, :password_hash, :nombre)',
            [
                'rol_id' => $rolId,
                'usuario' => $nombreUsuario,
                'password_hash' => $passwordHash,
                'nombre' => $nombre,
            ]
        );

        return $this->ultimoIdInsertado();
    }

    public function buscarActivo(string $nombreUsuario): ?Usuario
    {
        return $this->hidratarEntidad(
            Usuario::class,
            'SELECT u.*, r.nombre AS rol,
                    (u.bloqueado_hasta IS NOT NULL AND u.bloqueado_hasta > NOW()) AS bloqueado_actual
             FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.usuario = :usuario
               AND u.estado = "Activo"
             LIMIT 1',
            ['usuario' => $nombreUsuario]
        );
    }

    public function registrarFallo(int $usuarioId): void
    {
        $this->ejecutar(
            'UPDATE usuarios
             SET bloqueado_hasta = CASE
                     WHEN intentos_fallidos + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                     ELSE bloqueado_hasta
                 END,
                 intentos_fallidos = intentos_fallidos + 1
             WHERE id = :id',
            ['id' => $usuarioId]
        );
    }

    public function registrarAccesoCorrecto(int $usuarioId): void
    {
        $this->ejecutar(
            'UPDATE usuarios
             SET intentos_fallidos = 0,
                 bloqueado_hasta = NULL,
                 ultimo_acceso = NOW()
             WHERE id = :id',
            ['id' => $usuarioId]
        );
    }

    public function registrarIntento(
        string $nombreUsuario,
        string $direccionIp,
        bool $exitoso,
        string $motivo
    ): void {
        $this->ejecutar(
            'INSERT INTO intentos_acceso
                (usuario, direccion_ip, exitoso, motivo)
             VALUES
                (:usuario, :direccion_ip, :exitoso, :motivo)',
            [
                'usuario' => mb_substr($nombreUsuario, 0, 50),
                'direccion_ip' => mb_substr($direccionIp, 0, 45),
                'exitoso' => $exitoso ? 1 : 0,
                'motivo' => mb_substr($motivo, 0, 120),
            ]
        );
    }
}
