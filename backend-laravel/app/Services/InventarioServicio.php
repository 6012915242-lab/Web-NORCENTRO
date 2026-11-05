<?php

namespace App\Services;

use App\AccesoDatos\MotoDAO;
use App\Models\UnidadMoto;
use App\Servicios\GestionMotosServicio;
use Illuminate\Support\Facades\DB;

class InventarioServicio
{
    public function listar(): array
    {
        $dao = new MotoDAO(DB::connection()->getPdo());

        return $dao->inventario();
    }

    public function registrar(array $entrada): UnidadMoto
    {
        $dao = new MotoDAO(DB::connection()->getPdo());
        $identificador = (new GestionMotosServicio())->validarYRegistrarUnidad($dao, $entrada);

        return UnidadMoto::query()->findOrFail($identificador);
    }
}
