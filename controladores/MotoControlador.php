<?php

declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\MotoDAO;
use App\Configuracion\Herramientas;
use App\Servicios\GestionMotosServicio;
use DomainException;

/** Controlador delgado de motocicletas e inventario: delega la validación y el guardado en GestionMotosServicio. */
final class MotoControlador
{
    private GestionMotosServicio $servicioComercial;

    public function __construct(
        private MotoDAO $dao,
        ?GestionMotosServicio $servicioComercial = null
    ) {
        $this->servicioComercial = $servicioComercial ?? new GestionMotosServicio;
    }

    public function guardar(array $post, array $archivos, bool $esEdicion = false): never
    {
        try {
            $this->servicioComercial->validarYGuardarMoto(
                $this->dao,
                $post,
                $archivos,
                $esEdicion
            );
            $mensaje = $esEdicion
                ? 'Motocicleta y galería actualizadas.'
                : 'Motocicleta registrada con colores, fotografías y unidades disponibles.';
            Herramientas::mostrarAviso('success', $mensaje);
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('motos');
    }

    public function desactivar(int $id): never
    {
        try {
            $this->servicioComercial->desactivarMoto($this->dao, $id);
            Herramientas::mostrarAviso('success', 'Motocicleta desactivada.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        Herramientas::redirigir('motos');
    }

    /** Guarda el número de serie y el color de una unidad del inventario. */
    public function actualizarUnidad(array $post): never
    {
        try {
            $this->servicioComercial->validarYActualizarUnidad($this->dao, $post);
            Herramientas::mostrarAviso('success', 'Unidad actualizada.');
        } catch (DomainException $error) {
            Herramientas::mostrarAviso('error', $error->getMessage());
        }

        // Vuelve a la misma fila y con el mismo filtro que tenía el usuario.
        $filtro = array_filter([
            'inventario_q' => mb_substr((string) ($post['inventario_q'] ?? ''), 0, 100),
            'estado_inventario' => (string) ($post['estado_inventario'] ?? ''),
        ], static fn (string $valor): bool => $valor !== '');
        Herramientas::redirigir('inventario', $filtro, 'unidad-'.(int) ($post['unidad_id'] ?? 0));
    }
}
