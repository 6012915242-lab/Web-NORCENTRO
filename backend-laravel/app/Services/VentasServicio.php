<?php

namespace App\Services;

use App\AccesoDatos\VentaDAO;
use App\Servicios\EvaluacionCreditoServicio;
use Illuminate\Support\Facades\DB;

class VentasServicio
{
    public function listar(): array
    {
        return $this->dao()->listar();
    }

    public function registrar(array $datos, int $usuarioId): array
    {
        return (new EvaluacionCreditoServicio())->procesarRegistroVenta($this->dao(), $datos, $usuarioId);
    }

    public function listarCreditos(): array
    {
        return $this->dao()->creditos();
    }

    public function cuotas(int $creditoId): array
    {
        return DB::table('cuotas')
            ->where('credito_id', $creditoId)
            ->orderBy('numero')
            ->get()
            ->all();
    }

    public function registrarPago(array $datos): void
    {
        (new EvaluacionCreditoServicio())->procesarPagoCuota($this->dao(), $datos);
    }

    private function dao(): VentaDAO
    {
        return new VentaDAO(DB::connection()->getPdo());
    }
}
