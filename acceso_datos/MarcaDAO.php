<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Marca;
use App\Nucleo\ModeloBaseORM;

/** Acceso a la tabla marcas. Usa las operaciones genéricas del ORM (buscarPorId, buscarEntidadesPor...). */
final class MarcaDAO extends ModeloBaseORM
{
    protected const TABLA = 'marcas';

    protected const ENTIDAD = Marca::class;
}
