<?php

namespace App\Services;

use App\AccesoDatos\ClienteDAO;
use App\Models\Cliente;
use App\Servicios\GestionClientesServicio;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PDOException;

class ClienteServicio
{
    public function listar(?string $buscar): LengthAwarePaginator
    {
        return Cliente::query()
            ->select(['id', 'dni', 'nombres', 'apellidos', 'telefono', 'direccion', 'correo', 'estado'])
            ->where('estado', 'Activo')
            ->when($buscar !== null && $buscar !== '', function ($consulta) use ($buscar): void {
                $consulta->where(function ($subconsulta) use ($buscar): void {
                    $subconsulta->where('dni', 'like', "%{$buscar}%")
                        ->orWhere('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(20);
    }

    public function buscarActivo(int $id): Cliente
    {
        return Cliente::query()
            ->select(['id', 'dni', 'nombres', 'apellidos', 'telefono', 'direccion', 'correo', 'estado'])
            ->where('estado', 'Activo')
            ->findOrFail($id);
    }

    public function guardar(array $entrada, ?Cliente $cliente = null): Cliente
    {
        if ($cliente !== null) {
            $entrada['id'] = $cliente->id;
        }

        try {
            $identificador = (new GestionClientesServicio())->validarYGuardarCliente(
                $this->dao(),
                $entrada,
                $cliente !== null
            );
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['cliente' => $error->getMessage()]);
        } catch (PDOException $error) {
            $codigo = (int) ($error->errorInfo[1] ?? 0);
            if ($codigo === 1062 || ($codigo === 19 && str_contains($error->getMessage(), 'UNIQUE'))) {
                throw ValidationException::withMessages(['dni' => 'Ya existe un cliente con ese DNI.']);
            }

            throw $error;
        }

        return $this->buscarActivo($identificador);
    }

    public function desactivar(Cliente $cliente): void
    {
        try {
            (new GestionClientesServicio())->desactivarCliente($this->dao(), (int) $cliente->id);
        } catch (DomainException $error) {
            throw ValidationException::withMessages(['cliente' => $error->getMessage()]);
        }
    }

    private function dao(): ClienteDAO
    {
        return new ClienteDAO(DB::connection()->getPdo());
    }
}
