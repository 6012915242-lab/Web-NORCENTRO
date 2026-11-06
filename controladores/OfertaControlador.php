<?php

declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\OfertaDAO;
use App\Configuracion\Herramientas;
use App\Servicios\GestionOfertasServicio;
use DomainException;

/** Controlador delgado de ofertas: delega las reglas de precios y fechas en GestionOfertasServicio. */
final class OfertaControlador
{
    private GestionOfertasServicio $servicioComercial;

    public function __construct(
        private OfertaDAO $dao,
        ?GestionOfertasServicio $servicioComercial = null
    ) {
        $this->servicioComercial = $servicioComercial ?? new GestionOfertasServicio;
    }

    public function guardar(array $post): never
    {
        try {
            $this->servicioComercial->validarYGuardarOferta($this->dao, $post);
            Herramientas::mostrarAviso('success', 'Oferta registrada. El banner público se actualizará según sus fechas.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('ofertas');
    }

    public function desactivar(int $id): never
    {
        try {
            $this->servicioComercial->desactivarOferta($this->dao, $id);
            Herramientas::mostrarAviso('success', 'Oferta desactivada.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('ofertas');
    }
}
