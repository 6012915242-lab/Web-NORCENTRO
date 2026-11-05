<?php

namespace App\Services;

use App\AccesoDatos\MotoDAO;
use App\Models\Moto;
use App\Models\UnidadMoto;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventarioServicio
{
    public function listar(): array
    {
        $dao = new MotoDAO(DB::connection()->getPdo());

        return $dao->inventario();
    }

    public function registrar(array $entrada): UnidadMoto
    {
        $entrada['codigo_serie'] = strtoupper(trim((string) ($entrada['codigo_serie'] ?? '')));
        $datos = Validator::make($entrada, [
            'moto_id' => ['required', 'integer', 'min:1'],
            'codigo_serie' => ['required', 'string', 'regex:/^[A-Z0-9][A-Z0-9-]{2,49}$/'],
        ])->validate();

        try {
            return DB::transaction(function () use ($datos): UnidadMoto {
                $moto = Moto::query()
                    ->where('estado', 'Activo')
                    ->whereHas('marca', fn ($consulta) => $consulta->where('estado', 'Activo'))
                    ->lockForUpdate()
                    ->find($datos['moto_id']);
                if ($moto === null) {
                    throw new DomainException('Selecciona una motocicleta activa.');
                }

                $unidad = new UnidadMoto();
                $unidad->moto_id = $moto->id;
                $unidad->codigo_serie = $datos['codigo_serie'];
                $unidad->estado = 'Disponible';
                $unidad->save();

                return $unidad;
            });
        } catch (QueryException $error) {
            if (in_array((string) $error->getCode(), ['23000', '23505'], true)) {
                throw new DomainException('Ese código de serie ya está registrado.', 0, $error);
            }

            throw $error;
        }
    }
}
