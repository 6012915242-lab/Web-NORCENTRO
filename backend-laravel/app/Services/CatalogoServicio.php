<?php

namespace App\Services;

use App\Models\Moto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CatalogoServicio
{
    private function consultaPublica(): Builder
    {
        return Moto::query()
            ->where('estado', 'Activo')
            ->whereHas('marca', static fn (Builder $consulta) => $consulta->where('estado', 'Activo'))
            ->with(['marca:id,nombre,estado', 'colores.imagenes', 'imagenes'])
            ->withCount(['unidades as disponibles' => static function (Builder $consulta): void {
                $consulta->where('estado', 'Disponible');
            }]);
    }

    public function listar(string $buscar = ''): LengthAwarePaginator
    {
        return $this->consultaPublica()
            ->when($buscar !== '', static function (Builder $consulta) use ($buscar): void {
                $consulta->where('modelo', 'like', '%'.$buscar.'%');
            })
            ->orderBy('modelo')
            ->paginate(20);
    }

    public function obtener(int $id): Moto
    {
        return $this->consultaPublica()->findOrFail($id);
    }
}
