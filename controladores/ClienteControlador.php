<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\ClienteDAO;
use App\Configuracion\Herramientas;
use App\Servicios\GestionComercialServicio;
use DomainException;

final class ClienteControlador
{
    private GestionComercialServicio $servicioComercial;

    public function __construct(
        private ClienteDAO $dao,
        ?GestionComercialServicio $servicioComercial = null
    ) {
        $this->servicioComercial = $servicioComercial ?? new GestionComercialServicio();
    }

    public function guardar(array $post, bool $esEdicion = false): never
    {
        $continuarVenta = !$esEdicion && ((string) ($post['continuar_venta'] ?? '') === '1');

        try {
            $idCliente = $this->servicioComercial->validarYGuardarCliente($this->dao, $post, $esEdicion);

            if ($continuarVenta) {
                Herramientas::mostrarAviso('success', 'Cliente registrado. Ya está seleccionado para la nueva venta.');
                Herramientas::redirigir('ventas', ['cliente_id' => $idCliente]);
            }

            $mensaje = $esEdicion ? 'Cliente actualizado.' : 'Cliente registrado.';
            $params  = (!$esEdicion && $idCliente > 0) ? ['cliente_registrado' => $idCliente] : [];

            Herramientas::mostrarAviso('success', $mensaje);
            Herramientas::redirigir('clientes', $params);
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
            Herramientas::redirigir('clientes', $continuarVenta ? ['next' => 'venta'] : []);
        }
    }

    public function desactivar(int $id): never
    {
        if ($id > 0) {
            $this->dao->desactivar($id);
        }

        Herramientas::mostrarAviso('success', 'Cliente desactivado.');
        Herramientas::redirigir('clientes');
    }
}
