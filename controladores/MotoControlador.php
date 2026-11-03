<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\MotoDAO;
use App\Configuracion\Herramientas;
use App\Servicios\GestionComercialServicio;
use DomainException;

final class MotoControlador
{
    private GestionComercialServicio $servicioComercial;

    public function __construct(
        private MotoDAO $dao,
        ?GestionComercialServicio $servicioComercial = null
    ) {
        $this->servicioComercial = $servicioComercial ?? new GestionComercialServicio();
    }

    public function guardar(array $post, ?array $archivo, bool $esEdicion = false): never
    {
        try {
            $this->servicioComercial->validarYGuardarMoto($this->dao, $post, $archivo, $esEdicion);
            $mensaje = $esEdicion ? 'Motocicleta actualizada.' : 'Motocicleta registrada con sus unidades disponibles.';
            Herramientas::mostrarAviso('success', $mensaje);
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('motos');
    }

    public function desactivar(int $id): never
    {
        if ($id > 0) {
            $this->dao->desactivar($id);
        }

        Herramientas::mostrarAviso('success', 'Motocicleta desactivada.');
        Herramientas::redirigir('motos');
    }

    public function agregarUnidadInventario(array $post): never
    {
        $idMoto = filter_var($post['moto_id'] ?? null, FILTER_VALIDATE_INT);
        $serie  = strtoupper(trim((string) ($post['codigo_serie'] ?? '')));

        if (!$idMoto || $idMoto < 1 || !preg_match('/^[A-Z0-9][A-Z0-9-]{2,49}$/', $serie)) {
            Herramientas::mostrarAviso('error', 'Selecciona una moto y escribe un código de serie válido (3 a 50 letras, números o guiones).');
            Herramientas::redirigir('inventario');
        }

        try {
            $this->dao->registrarUnidad((int) $idMoto, $serie);
            Herramientas::mostrarAviso('success', 'Unidad registrada en el inventario.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('inventario');
    }
}
