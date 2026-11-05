<?php

namespace App\Services;

use App\Models\Moto;
use App\Models\Oferta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
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
        $datos = Validator::make($entrada, [
            'moto_id' => ['required', 'integer', 'min:1'],
            'titulo' => ['required', 'string', 'min:3', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'precio_normal' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'precio_oferta' => ['required', 'numeric', 'gt:0', 'lt:precio_normal'],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
        ])->validate();

        $motoActiva = Moto::query()
            ->where('estado', 'Activo')
            ->whereHas('marca', fn ($consulta) => $consulta->where('estado', 'Activo'))
            ->whereKey($datos['moto_id'])
            ->exists();

        if (! $motoActiva) {
            throw ValidationException::withMessages([
                'moto_id' => 'Selecciona una motocicleta y una marca activas.',
            ]);
        }

        $datos['descripcion'] = $datos['descripcion'] ?? '';
        $oferta ??= new Oferta();
        $oferta->fill($datos);

        if (! $oferta->exists) {
            $oferta->estado = 'Activa';
        }

        $oferta->save();

        return $oferta;
    }

    public function desactivar(Oferta $oferta): void
    {
        $oferta->estado = 'Inactiva';
        $oferta->save();
    }
}
