<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Nucleo\ModeloBaseORM;

/** Registra, renueva y revoca sesiones autenticadas. */
final class SesionDAO extends ModeloBaseORM
{
    public function registrar(int $usuarioId, string $identificadorSesion): void
    {
        $this->ejecutar(
            'INSERT INTO sesiones_usuario
                (usuario_id, identificador_hash, direccion_ip, agente_usuario, vence_en)
             VALUES
                (:usuario_id, :identificador_hash, :direccion_ip, :agente_usuario,
                 DATE_ADD(NOW(), INTERVAL 30 MINUTE))',
            [
                'usuario_id' => $usuarioId,
                'identificador_hash' => $this->crearHash($identificadorSesion),
                'direccion_ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'agente_usuario' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    }

    public function renovarSiVigente(int $usuarioId, string $identificadorSesion): bool
    {
        $sesion = $this->obtenerUno(
            'SELECT sesiones.id
             FROM sesiones_usuario AS sesiones
             INNER JOIN usuarios ON usuarios.id = sesiones.usuario_id
             WHERE sesiones.usuario_id = :usuario_id
               AND sesiones.identificador_hash = :identificador_hash
               AND sesiones.revocada_en IS NULL
               AND sesiones.vence_en > NOW()
               AND usuarios.estado = "Activo"
             LIMIT 1',
            [
                'usuario_id' => $usuarioId,
                'identificador_hash' => $this->crearHash($identificadorSesion),
            ]
        );

        if ($sesion === null) {
            return false;
        }

        $this->ejecutar(
            'UPDATE sesiones_usuario
             SET ultima_actividad = NOW(),
                 vence_en = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
             WHERE id = :id AND revocada_en IS NULL',
            ['id' => (int) $sesion['id']]
        );

        return true;
    }

    public function revocar(int $usuarioId, string $identificadorSesion): void
    {
        $this->ejecutar(
            'UPDATE sesiones_usuario
             SET revocada_en = NOW()
             WHERE usuario_id = :usuario_id
               AND identificador_hash = :identificador_hash
               AND revocada_en IS NULL',
            [
                'usuario_id' => $usuarioId,
                'identificador_hash' => $this->crearHash($identificadorSesion),
            ]
        );
    }

    private function crearHash(string $identificadorSesion): string
    {
        return hash('sha256', $identificadorSesion);
    }
}
