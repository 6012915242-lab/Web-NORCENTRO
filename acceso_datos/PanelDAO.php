<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Nucleo\ModeloBaseORM;

/** Obtiene los totales y resúmenes que muestra el panel. */
final class PanelDAO extends ModeloBaseORM
{
    public function resumen(): array
    {
        return [
            'motos' => (int) $this->conexion->query("SELECT COUNT(*) FROM motos WHERE estado='Activo'")->fetchColumn(),
            'disponibles' => (int) $this->conexion->query("
                SELECT COUNT(*)
                FROM unidades_moto unidad
                JOIN motos moto ON moto.id = unidad.moto_id
                JOIN marcas marca ON marca.id = moto.marca_id
                WHERE unidad.estado = 'Disponible'
                  AND moto.estado = 'Activo'
                  AND marca.estado = 'Activo'
            ")->fetchColumn(),
            'clientes' => (int) $this->conexion->query("SELECT COUNT(*) FROM clientes WHERE estado='Activo'")->fetchColumn(),
            'ventas' => (int) $this->conexion->query("SELECT COUNT(*) FROM ventas WHERE estado='Confirmada'")->fetchColumn(),
            'creditos' => (int) $this->conexion->query("SELECT COUNT(*) FROM creditos WHERE estado='Activo'")->fetchColumn(),
            'cuotas' => (int) $this->conexion->query("SELECT COUNT(*) FROM cuotas WHERE estado IN ('Pendiente','Vencida')")->fetchColumn(),
            'ofertas' => (int) $this->conexion->query("SELECT COUNT(*) FROM ofertas WHERE estado='Activa' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin")->fetchColumn(),
        ];
    }
}
