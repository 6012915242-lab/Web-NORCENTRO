<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Marca;
use App\Modelos\Moto;
use App\Nucleo\ModeloBaseORM;
use PDO;

/** Ejecuta consultas de catálogo, modelos, fotos e inventario de motos. */
final class MotoDAO extends ModeloBaseORM
{
    protected const TABLA = 'motos';
    protected const ENTIDAD = Moto::class;

    public function listar(string $buscar = ''): array
    {
        $consultaSQL = "
            SELECT m.*, ma.nombre AS marca,
                   (SELECT COUNT(*) FROM unidades_moto u
                    WHERE u.moto_id = m.id AND u.estado = 'Disponible') AS disponibles
            FROM motos m
            JOIN marcas ma ON ma.id = m.marca_id
            WHERE m.estado = 'Activo' AND ma.estado = 'Activo'
        ";
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (ma.nombre LIKE :term1 OR m.modelo LIKE :term2)';
            $terminoBusqueda = '%'.$buscar.'%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY ma.nombre,m.modelo';
        $consulta = $this->preparar($consultaSQL, $parametros);

        return $consulta->fetchAll();
    }

    public function obtener(int $id): ?array
    {
        $consulta = $this->conexion->prepare('
            SELECT m.*, ma.nombre AS marca
            FROM motos m
            INNER JOIN marcas ma ON ma.id = m.marca_id
            WHERE m.id = :id AND ma.estado = "Activo"
        ');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch() ?: null;
    }

    /** Mapea una fila de motos a una entidad del dominio. */
    public function obtenerEntidad(int $id): ?Moto
    {
        return $this->hidratarEntidad(
            Moto::class,
            'SELECT m.*, ma.nombre AS marca
             FROM motos m
             INNER JOIN marcas ma ON ma.id = m.marca_id
             WHERE m.id = :id',
            ['id' => $id]
        );
    }

    /** Marca por su id, usando la relación N:1 del ORM (Moto -> Marca). */
    public function marcaPorId(int $marcaId): ?Marca
    {
        $marca = $this->perteneceA(MarcaDAO::class, $marcaId);

        return $marca instanceof Marca ? $marca : null;
    }

    public function obtenerMarca(Moto $moto): ?Marca
    {
        $marca = $this->perteneceA(MarcaDAO::class, $moto->marcaId);

        return $marca instanceof Marca ? $marca : null;
    }

    public function detallePublico(int $id): ?array
    {
        $consulta = $this->conexion->prepare("
            SELECT m.*, ma.nombre AS marca,
                   (SELECT COUNT(*) FROM unidades_moto u
                    WHERE u.moto_id = m.id AND u.estado = 'Disponible') AS disponibles
            FROM motos m
            JOIN marcas ma ON ma.id = m.marca_id
            WHERE m.id = :id AND m.estado = 'Activo' AND ma.estado = 'Activo'
        ");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch() ?: null;
    }

    public function imagenesPublicas(int $id): array
    {
        $consulta = $this->preparar('
            SELECT ruta, texto_alt, orden, es_principal
            FROM moto_imagenes
            WHERE moto_id = :id
            ORDER BY es_principal DESC, orden, id
        ', ['id' => $id]);

        return $consulta->fetchAll();
    }

    public function coloresPublicos(int $id): array
    {
        return $this->coloresPorMotos([$id])[$id] ?? [];
    }

    public function coloresPorMotos(array $identificadores): array
    {
        $identificadores = array_values(array_unique(array_filter(
            array_map('intval', $identificadores),
            static fn (int $id): bool => $id > 0
        )));

        if ($identificadores === []) {
            return [];
        }

        $marcadores = implode(', ', array_fill(0, count($identificadores), '?'));
        $consulta = $this->preparar("
            SELECT c.moto_id, c.id AS color_id, c.nombre, c.codigo_hex,
                   i.ruta, i.texto_alt
            FROM moto_colores c
            JOIN moto_color_imagenes i ON i.color_id = c.id
            WHERE c.moto_id IN ($marcadores)
            ORDER BY c.moto_id, c.orden, i.orden, i.id
        ", $identificadores);
        $coloresPorMoto = [];

        foreach ($consulta->fetchAll() as $fila) {
            $idMoto = (int) $fila['moto_id'];
            $idColor = (int) $fila['color_id'];

            if (! isset($coloresPorMoto[$idMoto][$idColor])) {
                $coloresPorMoto[$idMoto][$idColor] = [
                    'id' => $idColor,
                    'nombre' => $fila['nombre'],
                    'codigo_hex' => $fila['codigo_hex'],
                    'imagenes' => [],
                ];
            }

            $coloresPorMoto[$idMoto][$idColor]['imagenes'][] = [
                'ruta' => $fila['ruta'],
                'texto_alt' => $fila['texto_alt'],
            ];
        }

        foreach ($coloresPorMoto as $idMoto => $colores) {
            $coloresPorMoto[$idMoto] = array_values($colores);
        }

        return $coloresPorMoto;
    }

    public function especificacionesPublicas(int $id): array
    {
        $consulta = $this->preparar('
            SELECT grupo, nombre, valor, fuente_url
            FROM moto_especificaciones
            WHERE moto_id = :id
            ORDER BY orden, id
        ', ['id' => $id]);

        return $consulta->fetchAll();
    }

    public function marcas(): array
    {
        return $this->preparar('SELECT id,nombre FROM marcas WHERE estado="Activo" ORDER BY nombre')->fetchAll();
    }

    public function marcaActiva(int $id): bool
    {
        $consulta = $this->conexion->prepare('SELECT 1 FROM marcas WHERE id=:id AND estado="Activo"');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return (bool) $consulta->fetchColumn();
    }

    public function guardar(array $datos): int
    {
        return $this->ejecutarEnTransaccion(function () use ($datos): int {
            $id = $this->insertar($this->columnasEditables($datos) + ['estado' => 'Activo']);
            // Unidades sin color (compatibilidad con el backend Laravel, que envía 'cantidad').
            $this->crearUnidades($id, null, (int) ($datos['cantidad'] ?? 0));

            return $id;
        });
    }

    public function guardarConGaleria(array $datos, array $colores): int
    {
        return $this->ejecutarEnTransaccion(function () use ($datos, $colores): int {
            $id = $this->guardar($datos);
            $idsColores = $this->guardarGaleriaColores($id, $colores);
            $this->crearUnidadesPorColor($id, $colores, $idsColores);
            $this->asignarColorUnicoASinColor($id);

            return $id;
        });
    }

    /**
     * Actualiza la ficha, agrega colores nuevos y crea unidades.
     *
     * @param  array<int, int>  $unidadesColoresExistentes  [color_id => cantidad] para colores ya registrados
     */
    public function actualizarConGaleria(int $id, array $datos, array $colores, array $unidadesColoresExistentes = []): void
    {
        $this->ejecutarEnTransaccion(function () use ($id, $datos, $colores, $unidadesColoresExistentes): void {
            $this->actualizar($id, $datos);
            $idsColores = $this->guardarGaleriaColores($id, $colores);
            $this->crearUnidadesPorColor($id, $colores, $idsColores);

            foreach ($unidadesColoresExistentes as $idColor => $cantidad) {
                if (! $this->colorPerteneceAMoto((int) $idColor, $id)) {
                    throw new \DomainException('Uno de los colores no corresponde a esta motocicleta.');
                }
                $this->crearUnidades($id, (int) $idColor, (int) $cantidad);
            }
            $this->asignarColorUnicoASinColor($id);
        });
    }

    /** Crea las unidades indicadas en cada color del formulario ('unidades' => cantidad). */
    private function crearUnidadesPorColor(int $idMoto, array $colores, array $idsColores): void
    {
        foreach ($colores as $indice => $color) {
            $cantidad = (int) ($color['unidades'] ?? 0);
            if ($cantidad > 0 && isset($idsColores[$indice])) {
                $this->crearUnidades($idMoto, $idsColores[$indice], $cantidad);
            }
        }
    }

    /** Inserta unidades disponibles con códigos de serie internos consecutivos (NC-<moto>-001...). */
    private function crearUnidades(int $idMoto, ?int $idColor, int $cantidad): void
    {
        if ($cantidad < 1) {
            return;
        }

        $columnaColor = $idColor === null ? '' : ', color_id';
        $valorColor = $idColor === null ? '' : ', :color';
        $insertar = $this->conexion->prepare(
            "INSERT INTO unidades_moto (moto_id{$columnaColor}, codigo_serie, estado)
             VALUES (:moto{$valorColor}, :serie, 'Disponible')"
        );

        $numero = $this->ultimoNumeroSerieInterno($idMoto);
        for ($i = 0; $i < $cantidad; $i++) {
            $numero++;
            $parametros = ['moto' => $idMoto, 'serie' => $this->formatearSerieInterna($idMoto, $numero)];
            if ($idColor !== null) {
                $parametros['color'] = $idColor;
            }
            $insertar->execute($parametros);
        }
    }

    /** Código interno sugerido para la próxima unidad de un modelo (por ejemplo NC-55-006). */
    public function siguienteCodigoSerie(int $idMoto): string
    {
        return $this->formatearSerieInterna($idMoto, $this->ultimoNumeroSerieInterno($idMoto) + 1);
    }

    private function ultimoNumeroSerieInterno(int $idMoto): int
    {
        $prefijo = 'NC-'.$idMoto.'-';
        $codigos = $this->preparar(
            'SELECT codigo_serie FROM unidades_moto WHERE codigo_serie LIKE :prefijo',
            ['prefijo' => $prefijo.'%']
        )->fetchAll(PDO::FETCH_COLUMN);

        $ultimo = 0;
        foreach ($codigos as $codigo) {
            $sufijo = substr((string) $codigo, strlen($prefijo));
            if (ctype_digit($sufijo)) {
                $ultimo = max($ultimo, (int) $sufijo);
            }
        }

        return $ultimo;
    }

    private function formatearSerieInterna(int $idMoto, int $numero): string
    {
        return 'NC-'.$idMoto.'-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }

    /** Si el modelo tiene un solo color, las unidades sin color toman ese color. */
    private function asignarColorUnicoASinColor(int $idMoto): void
    {
        $colores = $this->obtenerTodos('SELECT id FROM moto_colores WHERE moto_id = :moto', ['moto' => $idMoto]);
        if (count($colores) !== 1) {
            return;
        }

        $this->ejecutar(
            'UPDATE unidades_moto SET color_id = :color WHERE moto_id = :moto AND color_id IS NULL',
            ['color' => (int) $colores[0]['id'], 'moto' => $idMoto]
        );
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->actualizarPorId($id, $this->columnasEditables($datos));
    }

    private function columnasEditables(array $datos): array
    {
        $columnas = ['marca_id', 'modelo', 'cilindrada', 'color', 'precio', 'descripcion', 'anio', 'imagen'];

        return array_intersect_key($datos, array_flip($columnas));
    }

    /**
     * Guarda colores y varias tomas por color sin eliminar la galería existente.
     * Devuelve el id de cada color en el mismo orden del arreglo recibido.
     */
    public function guardarGaleriaColores(int $motoId, array $colores): array
    {
        if ($colores === []) {
            return [];
        }

        return $this->ejecutarEnTransaccion(function () use ($motoId, $colores): array {
            $idsColores = [];
            $consultaOrdenColor = $this->preparar(
                'SELECT COALESCE(MAX(orden), 0) FROM moto_colores WHERE moto_id = :moto_id',
                ['moto_id' => $motoId]
            );
            $ordenColor = (int) $consultaOrdenColor->fetchColumn();

            $guardarColor = $this->conexion->prepare(
                'INSERT INTO moto_colores (moto_id, nombre, codigo_hex, orden)
                 VALUES (:moto_id, :nombre, :codigo_hex, :orden)
                 ON DUPLICATE KEY UPDATE
                    id = LAST_INSERT_ID(id),
                    codigo_hex = VALUES(codigo_hex)'
            );
            $consultarOrdenImagen = $this->conexion->prepare(
                'SELECT COALESCE(MAX(orden), 0)
                 FROM moto_color_imagenes
                 WHERE color_id = :color_id'
            );
            $guardarImagen = $this->conexion->prepare(
                'INSERT INTO moto_color_imagenes (color_id, ruta, texto_alt, orden)
                 VALUES (:color_id, :ruta, :texto_alt, :orden)'
            );

            foreach ($colores as $indiceColor => $color) {
                $ordenColor++;
                $guardarColor->execute([
                    'moto_id' => $motoId,
                    'nombre' => $color['nombre'],
                    'codigo_hex' => $color['codigo_hex'],
                    'orden' => $ordenColor,
                ]);
                $colorId = (int) $this->conexion->lastInsertId();
                $idsColores[$indiceColor] = $colorId;

                $consultarOrdenImagen->execute(['color_id' => $colorId]);
                $ordenImagen = (int) $consultarOrdenImagen->fetchColumn();

                foreach ($color['imagenes'] as $imagen) {
                    $ordenImagen++;
                    $guardarImagen->execute([
                        'color_id' => $colorId,
                        'ruta' => $imagen['ruta'],
                        'texto_alt' => $imagen['texto_alt'],
                        'orden' => $ordenImagen,
                    ]);
                }
            }

            return $idsColores;
        });
    }

    public function desactivar(int $id): bool
    {
        $filasActualizadas = $this->ejecutar(
            'UPDATE motos SET estado = :nuevo_estado WHERE id = :id AND estado = :estado_actual',
            [
                'nuevo_estado' => 'Inactivo',
                'estado_actual' => 'Activo',
                'id' => $id,
            ]
        );

        return $filasActualizadas === 1;
    }

    public function unidadesDisponibles(string $buscar = '', int $limite = 30): array
    {
        $limite = max(1, min(100, $limite));
        $consultaSQL = "
            SELECT u.id AS unidad_id, u.codigo_serie, m.id AS moto_id,
                   m.modelo, m.precio, m.imagen, ma.nombre AS marca,
                   u.color_id, mc.nombre AS color, mc.codigo_hex
            FROM unidades_moto u
            JOIN motos m ON m.id = u.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            LEFT JOIN moto_colores mc ON mc.id = u.color_id
            WHERE u.estado = 'Disponible' AND m.estado = 'Activo' AND ma.estado = 'Activo'
        ";
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (ma.nombre LIKE :term1 OR m.modelo LIKE :term2 OR CONCAT(ma.nombre," ",m.modelo) LIKE :term3 OR u.codigo_serie LIKE :term4 OR mc.nombre LIKE :term5)';
            $terminoBusqueda = '%'.$buscar.'%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda,
                'term4' => $terminoBusqueda,
                'term5' => $terminoBusqueda];
        }
        // Las unidades sin color asignado quedan al final de cada modelo.
        $consultaSQL .= ' ORDER BY ma.nombre, m.modelo, u.color_id IS NULL, mc.orden, u.codigo_serie LIMIT '.$limite;
        $consulta = $this->preparar($consultaSQL, $parametros);

        return $consulta->fetchAll();
    }

    public function inventario(string $busqueda = '', string $estado = ''): array
    {
        $consulta = '
            SELECT u.id, u.moto_id, u.codigo_serie, u.estado, u.color_id,
                   mc.nombre AS color, mc.codigo_hex,
                   m.modelo, m.imagen, ma.nombre AS marca
            FROM unidades_moto u
            JOIN motos m ON m.id = u.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            LEFT JOIN moto_colores mc ON mc.id = u.color_id
            WHERE 1 = 1
        ';
        $parametros = [];

        if ($busqueda !== '') {
            $consulta .= ' AND (u.codigo_serie LIKE :serie OR m.modelo LIKE :modelo
                OR ma.nombre LIKE :marca OR mc.nombre LIKE :color)';
            $termino = '%'.$busqueda.'%';
            $parametros = [
                'serie' => $termino,
                'modelo' => $termino,
                'marca' => $termino,
                'color' => $termino,
            ];
        }

        if (in_array($estado, ['Disponible', 'Reservada', 'Vendida'], true)) {
            $consulta .= ' AND u.estado = :estado';
            $parametros['estado'] = $estado;
        }

        $consulta .= ' ORDER BY u.estado, ma.nombre, m.modelo, u.codigo_serie';

        return $this->obtenerTodos($consulta, $parametros);
    }

    /** Cambia el código de serie y el color de una unidad que todavía no se vendió. */
    public function actualizarUnidad(int $idUnidad, string $codigoSerie, ?int $idColor): void
    {
        $unidad = $this->obtenerUno(
            'SELECT id, moto_id, estado FROM unidades_moto WHERE id = :id',
            ['id' => $idUnidad]
        );

        if ($unidad === null || $unidad['estado'] === 'Vendida') {
            throw new \DomainException('Solo se puede modificar una unidad que no esté vendida.');
        }

        if ($idColor !== null && ! $this->colorPerteneceAMoto($idColor, (int) $unidad['moto_id'])) {
            throw new \DomainException('El color elegido no corresponde a esa motocicleta.');
        }

        try {
            $this->ejecutar(
                'UPDATE unidades_moto SET codigo_serie = :serie, color_id = :color WHERE id = :id',
                ['serie' => $codigoSerie, 'color' => $idColor, 'id' => $idUnidad]
            );
        } catch (\PDOException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) === 1062) {
                throw new \DomainException('Ese código de serie ya pertenece a otra unidad.', 0, $error);
            }

            throw $error;
        }
    }

    /**
     * Stock por modelo y color: cuántas unidades hay disponibles, reservadas y vendidas.
     * Las unidades sin color aparecen como "Color por asignar".
     */
    public function stockPorColor(): array
    {
        return $this->obtenerTodos(
            "SELECT m.id AS moto_id, ma.nombre AS marca, m.modelo,
                    COALESCE(mc.nombre, 'Color por asignar') AS color, mc.codigo_hex,
                    SUM(u.estado = 'Disponible') AS disponibles,
                    SUM(u.estado = 'Reservada') AS reservadas,
                    SUM(u.estado = 'Vendida') AS vendidas
             FROM unidades_moto u
             JOIN motos m ON m.id = u.moto_id
             JOIN marcas ma ON ma.id = m.marca_id
             LEFT JOIN moto_colores mc ON mc.id = u.color_id
             WHERE m.estado = 'Activo'
             GROUP BY m.id, ma.nombre, m.modelo, mc.id, mc.nombre, mc.codigo_hex, mc.orden
             ORDER BY ma.nombre, m.modelo, mc.id IS NULL, mc.orden"
        );
    }

    /** Unidades disponibles de una moto por color: [color_id => cantidad]. */
    public function disponiblesPorColor(int $idMoto): array
    {
        $filas = $this->obtenerTodos(
            "SELECT color_id, COUNT(*) AS total FROM unidades_moto
             WHERE moto_id = :moto AND estado = 'Disponible' AND color_id IS NOT NULL
             GROUP BY color_id",
            ['moto' => $idMoto]
        );

        return array_column(array_map(
            static fn (array $fila): array => ['id' => (int) $fila['color_id'], 'total' => (int) $fila['total']],
            $filas
        ), 'total', 'id');
    }

    /** Colores registrados para cada moto: [moto_id => [['id' => .., 'nombre' => ..], ...]]. */
    public function coloresParaInventario(): array
    {
        $colores = [];
        foreach ($this->obtenerTodos('SELECT id, moto_id, nombre FROM moto_colores ORDER BY moto_id, orden, id') as $fila) {
            $colores[(int) $fila['moto_id']][] = ['id' => (int) $fila['id'], 'nombre' => $fila['nombre']];
        }

        return $colores;
    }

    private function colorPerteneceAMoto(int $idColor, int $idMoto): bool
    {
        return $this->obtenerUno(
            'SELECT 1 FROM moto_colores WHERE id = :color AND moto_id = :moto',
            ['color' => $idColor, 'moto' => $idMoto]
        ) !== null;
    }

    /**
     * Registra una unidad física; el color es opcional y debe pertenecer al modelo.
     * La web crea las unidades desde Gestión de motos; este método lo usa la API Laravel.
     */
    public function registrarUnidad(int $idMoto, string $codigoSerie, ?int $idColor = null): int
    {
        try {
            return $this->ejecutarEnTransaccion(function () use ($idMoto, $codigoSerie, $idColor): int {
                $bloqueo = $this->conexion->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
                    ? ''
                    : ' FOR UPDATE';
                $consulta = $this->preparar(
                    'SELECT m.id FROM motos m
                     JOIN marcas ma ON ma.id = m.marca_id
                     WHERE m.id = :id AND m.estado = :estado AND ma.estado = :estado_marca'.$bloqueo,
                    [
                        'id' => $idMoto,
                        'estado' => 'Activo',
                        'estado_marca' => 'Activo',
                    ]
                );

                if (! $consulta->fetchColumn()) {
                    throw new \DomainException('Selecciona una motocicleta activa.');
                }

                if ($idColor !== null && ! $this->colorPerteneceAMoto($idColor, $idMoto)) {
                    throw new \DomainException('El color elegido no corresponde a esa motocicleta.');
                }

                // Sin número de serie del fabricante se usa el código interno consecutivo.
                if ($codigoSerie === '') {
                    $codigoSerie = $this->siguienteCodigoSerie($idMoto);
                }

                $this->ejecutar(
                    'INSERT INTO unidades_moto (moto_id, color_id, codigo_serie, estado)
                     VALUES (:moto, :color, :serie, :estado)',
                    [
                        'moto' => $idMoto,
                        'color' => $idColor,
                        'serie' => $codigoSerie,
                        'estado' => 'Disponible',
                    ]
                );

                return $this->ultimoIdInsertado();
            });
        } catch (\PDOException $error) {
            $codigo = (int) ($error->errorInfo[1] ?? 0);
            if ($codigo === 1062 || ($codigo === 19 && str_contains($error->getMessage(), 'UNIQUE'))) {
                throw new \DomainException('Ese código de serie ya está registrado.', 0, $error);
            }

            throw $error;
        }
    }
}
