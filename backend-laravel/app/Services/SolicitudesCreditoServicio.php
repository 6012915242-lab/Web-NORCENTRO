<?php

namespace App\Services;

use App\AccesoDatos\PedidoDAO;
use App\Servicios\EvaluacionCreditoServicio;
use Illuminate\Support\Facades\DB;

class SolicitudesCreditoServicio
{
    public function listar(): array
    {
        return $this->dao()->solicitudesCredito();
    }

    public function evaluar(int $id, string $decision): string
    {
        return (new EvaluacionCreditoServicio())->evaluarSolicitudCredito(
            $this->dao(),
            ['pedido_id' => $id, 'decision' => $decision]
        );
    }

    private function dao(): PedidoDAO
    {
        return new PedidoDAO(DB::connection()->getPdo());
    }
}
