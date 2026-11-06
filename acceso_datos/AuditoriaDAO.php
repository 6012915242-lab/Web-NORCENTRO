<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Nucleo\ModeloBaseORM;

/** Persiste eventos de seguridad y trazabilidad del sistema. */
final class AuditoriaDAO extends ModeloBaseORM
{
    public function registrar(array $evento): void
    {
        $this->ejecutar(
            'INSERT INTO auditoria_seguridad
                (usuario_id, accion, recurso, metodo_http, direccion_ip, resultado, datos)
             VALUES
                (:usuario_id, :accion, :recurso, :metodo_http, :direccion_ip, :resultado, :datos)',
            $evento
        );
    }
}
