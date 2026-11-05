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
            $datos = (new GestionOfertasServicio())->prepararOferta($this->dao(), $entrada);
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['oferta' => $error->getMessage()]);
        }

        $oferta ??= new Oferta();
        $oferta->fill([
            'moto_id' => $datos['moto'],
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'],
            'precio_normal' => $datos['normal'],
            'precio_oferta' => $datos['oferta'],
            'fecha_inicio' => $datos['inicio'],
            'fecha_fin' => $datos['fin'],
        ]);

        if (! $oferta->exists) {
            $oferta->estado = 'Activa';
        }

        $oferta->save();

        return $oferta;
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
