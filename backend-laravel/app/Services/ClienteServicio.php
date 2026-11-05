<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClienteServicio
{
    public function guardar(array $entrada, ?Cliente $cliente = null): Cliente
    {
        $datos = Validator::make($entrada, [
            'dni' => ['required', 'digits:8', Rule::unique('clientes', 'dni')->ignore($cliente?->id)],
            'nombres' => ['required', 'string', 'min:2', 'max:100'],
            'apellidos' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9 ()-]{7,20}$/'],
            'correo' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
        ])->validate();

        $cliente ??= new Cliente();
        $cliente->fill($datos);

        if (! $cliente->exists) {
            $cliente->estado = 'Activo';
        }

        $cliente->save();

        return $cliente;
    }

    public function desactivar(Cliente $cliente): void
    {
        $cliente->estado = 'Inactivo';
        $cliente->save();
    }
}
