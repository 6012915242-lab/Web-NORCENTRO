<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\OfertaDAO;
use App\Configuracion\Herramientas;
use App\Servicios\GestionComercialServicio;
use DomainException;

final class OfertaControlador
{
    private GestionComercialServicio $servicioComercial;

    public function __construct(
        private OfertaDAO $dao,
        ?GestionComercialServicio $servicioComercial = null
    ) {
        $this->servicioComercial = $servicioComercial ?? new GestionComercialServicio();
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
        if ($id > 0) {
            $this->dao->desactivar($id);
        }

        Herramientas::mostrarAviso('success', 'Oferta desactivada.');
        Herramientas::redirigir('ofertas');
    }
}
