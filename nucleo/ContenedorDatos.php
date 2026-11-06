<?php

declare(strict_types=1);

namespace App\Nucleo;

use App\AccesoDatos\AuditoriaDAO;
use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use App\AccesoDatos\UsuarioDAO;
use App\AccesoDatos\VentaDAO;
use PDO;

/** Reúne los DAO que comparten la conexión de la petición. */
final class ContenedorDatos
{
    public readonly UsuarioDAO $usuarios;
    public readonly MotoDAO $motos;
    public readonly ClienteDAO $clientes;
    public readonly VentaDAO $ventas;
    public readonly OfertaDAO $ofertas;
    public readonly PanelDAO $panel;
    public readonly PedidoDAO $pedidos;
    public readonly SesionDAO $sesiones;
    public readonly PermisoDAO $permisos;
    public readonly AuditoriaDAO $auditoria;

    public function __construct(PDO $conexion)
    {
        $this->usuarios = new UsuarioDAO($conexion);
        $this->motos = new MotoDAO($conexion);
        $this->clientes = new ClienteDAO($conexion);
        $this->ventas = new VentaDAO($conexion);
        $this->ofertas = new OfertaDAO($conexion);
        $this->panel = new PanelDAO($conexion);
        $this->pedidos = new PedidoDAO($conexion);
        $this->sesiones = new SesionDAO($conexion);
        $this->permisos = new PermisoDAO($conexion);
        $this->auditoria = new AuditoriaDAO($conexion);
    }
}
