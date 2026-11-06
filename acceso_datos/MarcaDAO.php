<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Marca;
use App\Nucleo\ModeloBaseORM;

/** Consulta las marcas. */
final class MarcaDAO extends ModeloBaseORM
{
    protected const TABLA = 'marcas';
    protected const ENTIDAD = Marca::class;
}
