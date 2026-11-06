<?php

namespace App\Services;

use App\AccesoDatos\VentaDAO;
use App\Modelos\Credito;
use App\Modelos\Venta;
use App\Servicios\EvaluacionCreditoServicio;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class VentasServicio
{
    public function listar(): array
    {
        return $this->dao()->listar();
    }

    public function registrar(array $datos, int $usuarioId): array
    {
        return (new EvaluacionCreditoServicio)->procesarRegistroVenta($this->dao(), $datos, $usuarioId);
    }

    public function obtener(int $id): Venta
    {
        $venta = $this->dao()->obtenerConRelaciones($id);

        if ($venta === null) {
            throw (new ModelNotFoundException)->setModel(Venta::class, [$id]);
        }

        return $venta;
    }

    public function listarCreditos(): array
    {
        return $this->dao()->creditos();
    }

    public function cuotas(int $creditoId): array
    {
        $ventasDAO = $this->dao();

        if ($ventasDAO->obtenerCredito($creditoId) === null) {
            throw (new ModelNotFoundException)->setModel(Credito::class, [$creditoId]);
        }

        return $ventasDAO->cuotasDeCredito($creditoId);
    }

    public function registrarPago(array $datos): void
    {
        (new EvaluacionCreditoServicio)->procesarPagoCuota($this->dao(), $datos);
    }

    private function dao(): VentaDAO
    {
        return new VentaDAO(DB::connection()->getPdo());
    }
}
