<?php

declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\ClienteDAO;
use DomainException;

/** Reglas de negocio de clientes: valida los datos, evita DNI duplicados y guarda mediante ClienteDAO. */
final class GestionClientesServicio
{
    public function desactivarCliente(ClienteDAO $clienteDAO, int $id): void
    {
        if ($id < 1 || ! $clienteDAO->desactivar($id)) {
            throw new DomainException('No se encontró un cliente activo con ese identificador.');
        }
    }

    public function validarYGuardarCliente(ClienteDAO $clienteDAO, array $entrada, bool $esEdicion = false): int
    {
        $id = filter_var($entrada['id'] ?? 0, FILTER_VALIDATE_INT);
        $dni = TextoEntrada::normalizar($entrada['dni'] ?? '');
        $nombres = TextoEntrada::normalizar($entrada['nombres'] ?? '');
        $apellidos = TextoEntrada::normalizar($entrada['apellidos'] ?? '');
        $telefono = TextoEntrada::normalizar($entrada['telefono'] ?? '');
        $direccion = TextoEntrada::normalizar($entrada['direccion'] ?? '');
        $correo = TextoEntrada::normalizar($entrada['correo'] ?? '');

        if ($esEdicion && (! $id || $id < 1)) {
            throw new DomainException('El identificador del cliente a editar no es válido.');
        }

        if ($esEdicion) {
            $clienteExistente = $clienteDAO->obtenerEntidad((int) $id);

            if ($clienteExistente === null || $clienteExistente->estado !== 'Activo') {
                throw new DomainException('No se encontró un cliente activo con ese identificador.');
            }
        }

        Validador::dni($dni, 'El DNI debe contener exactamente 8 dígitos numéricos.');

        $patronNombre = "/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u";

        if (mb_strlen($nombres) < 2 || mb_strlen($nombres) > 100 || ! preg_match($patronNombre, $nombres)) {
            throw new DomainException('Ingresa nombres válidos (entre 2 y 100 caracteres).');
        }

        if (mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 100 || ! preg_match($patronNombre, $apellidos)) {
            throw new DomainException('Ingresa apellidos válidos (entre 2 y 100 caracteres).');
        }

        if ($telefono !== '') {
            Validador::telefonoFlexible($telefono, 'Ingresa un número de teléfono válido (7 a 20 caracteres).');
        }

        if (mb_strlen($direccion) > 180 || mb_strlen($correo) > 120) {
            throw new DomainException('La dirección o el correo superan la longitud máxima permitida.');
        }

        if ($correo !== '') {
            Validador::correo($correo, 'Ingresa un correo válido.');
        }

        if ($clienteDAO->existeDni($dni, $esEdicion ? (int) $id : null)) {
            throw new DomainException('Ya existe un cliente con ese DNI.');
        }

        $datos = [
            'dni' => $dni,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'correo' => $correo,
        ];

        if ($esEdicion) {
            $clienteDAO->actualizar((int) $id, $datos);

            return (int) $id;
        }

        return $clienteDAO->guardar($datos);
    }
}
