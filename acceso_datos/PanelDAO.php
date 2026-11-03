<?php
declare(strict_types = 1);
namespace App\AccesoDatos;
use App\Nucleo\ModeloBaseORM;
/** Obtiene los totales y resúmenes que muestra el panel. */
final class PanelDAO extends ModeloBaseORM
{
    public function resumen(): array
    {
        return [
            'motos' => (int)$this->conexion->query("SELECT COUNT(*) FROM motos WHERE estado='Activo'")->fetchColumn(),
            'disponibles' => (int)$this->conexion->query("SELECT COUNT(*) FROM unidades_moto WHERE estado='Disponible'")->fetchColumn(),
            'clientes' => (int)$this->conexion->query("SELECT COUNT(*) FROM clientes WHERE estado='Activo'")->fetchColumn(),
            'ventas' => (int)$this->conexion->query("SELECT COUNT(*) FROM ventas WHERE estado='Confirmada'")->fetchColumn(),
            'creditos' => (int)$this->conexion->query("SELECT COUNT(*) FROM creditos WHERE estado='Activo'")->fetchColumn(),
            'cuotas' => (int)$this->conexion->query("SELECT COUNT(*) FROM cuotas WHERE estado IN ('Pendiente','Vencida')")->fetchColumn(),
            'ofertas' => (int)$this->conexion->query("SELECT COUNT(*) FROM ofertas WHERE estado='Activa' AND CURDATE() BETWEEN fecha_inicio AND fecha_fin")->fetchColumn(),
        ];
    }
}
