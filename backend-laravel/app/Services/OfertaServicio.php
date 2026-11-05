<?php

namespace App\Services;

use App\AccesoDatos\OfertaDAO;
use App\Models\Oferta;
use App\Servicios\GestionOfertasServicio;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfertaServicio
{
    public function listar(): LengthAwarePaginator
    {
        return Oferta::query()->with('moto.marca')->orderByDesc('fecha_fin')->paginate(20);
    }

    public function buscarActiva(int $id): Oferta
    {
        return Oferta::query()->where('estado', 'Activa')->findOrFail($id);
    }

    public function guardar(array $entrada, ?Oferta $oferta = null): Oferta
    {
        try {
            $servicio = new GestionOfertasServicio();
            if ($oferta === null) {
                $id = $servicio->validarYGuardarOferta($this->dao(), $entrada);
            } else {
                $id = (int) $oferta->id;
                $servicio->validarYActualizarOferta($this->dao(), $id, $entrada);
            }
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['oferta' => $error->getMessage()]);
        }

        return $this->buscarActiva($id);
    }

    public function desactivar(Oferta $oferta): void
    {
        try {
            (new GestionOfertasServicio())->desactivarOferta($this->dao(), (int) $oferta->id);
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['oferta' => $error->getMessage()]);
        }
    }

    private function dao(): OfertaDAO
    {
        return new OfertaDAO(DB::connection()->getPdo());
    }
}
