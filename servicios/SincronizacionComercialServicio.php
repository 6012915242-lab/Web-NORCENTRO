<?php

declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PedidoDAO;

/** Mantiene actualizados los estados que dependen de fechas y reservas. */
final class SincronizacionComercialServicio
{
    public function __construct(
        private OfertaDAO $ofertaDAO,
        private PedidoDAO $pedidoDAO
    ) {}

    public function actualizar(): void
    {
        $this->ofertaDAO->actualizarEstadoFechas();
        $this->pedidoDAO->liberarPedidosVencidos();
    }
}
