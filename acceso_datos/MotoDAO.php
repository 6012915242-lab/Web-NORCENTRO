<?php
declare(strict_types = 1);
namespace App\AccesoDatos;
use PDO;
use App\Modelos\Moto;
use App\Nucleo\ModeloBaseORM;
/** Ejecuta consultas de catálogo, modelos, fotos e inventario de motos. */
final class MotoDAO extends ModeloBaseORM
{
    public function listar(string $buscar = ''): array
    {
        $consultaSQL = "SELECT m.*, ma.nombre marca, (SELECT COUNT(*) FROM unidades_moto u WHERE u.moto_id=m.id AND u.estado='Disponible') disponibles FROM motos m JOIN marcas ma ON ma.id=m.marca_id WHERE m.estado='Activo' AND ma.estado='Activo'";
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (ma.nombre LIKE :term1 OR m.modelo LIKE :term2)';
            $terminoBusqueda = '%' . $buscar . '%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY ma.nombre,m.modelo';
        $consulta = $this->conexion->prepare($consultaSQL);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    public function obtener(int $id): ?array
    {
        $consulta = $this->conexion->prepare('SELECT m.*,ma.nombre marca FROM motos m JOIN marcas ma ON ma.id=m.marca_id WHERE m.id=:id AND ma.estado="Activo"');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()?:null;
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

    public function detallePublico(int $id): ?array
    {
        $consulta = $this->conexion->prepare("SELECT m.*,ma.nombre marca,(SELECT COUNT(*) FROM unidades_moto u WHERE u.moto_id=m.id AND u.estado='Disponible') disponibles FROM motos m JOIN marcas ma ON ma.id=m.marca_id WHERE m.id=:id AND m.estado='Activo' AND ma.estado='Activo'");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch()?:null;
    }

    public function imagenesPublicas(int $id): array {
        $consulta = $this->conexion->prepare('SELECT ruta,texto_alt,orden,es_principal FROM moto_imagenes WHERE moto_id=:id ORDER BY es_principal DESC,orden,id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetchAll();
    }

    public function coloresPublicos(int $id): array
    {
        $consulta = $this->conexion->prepare('SELECT c.id color_id,c.nombre,c.codigo_hex,c.orden color_orden,i.ruta,i.texto_alt,i.orden imagen_orden FROM moto_colores c JOIN moto_color_imagenes i ON i.color_id=c.id WHERE c.moto_id=:id ORDER BY c.orden,i.orden,i.id');
        $consulta->execute(['id' => $id]);
        $colores = [];
        foreach ($consulta->fetchAll() as $fila) {
            $idColor = (int)$fila['color_id'];
            if (!isset($colores[$idColor])) $colores[$idColor] = ['id' => $idColor,
                'nombre' => $fila['nombre'],
                'codigo_hex' => $fila['codigo_hex'],
                'imagenes' => []];
            $colores[$idColor]['imagenes'][] = ['ruta' => $fila['ruta'],
                'texto_alt' => $fila['texto_alt']];
        }
        return array_values(array_filter($colores, static fn($color) => count($color['imagenes']) > 0));
    }

    public function especificacionesPublicas(int $id): array {
        $consulta = $this->conexion->prepare('SELECT grupo,nombre,valor,fuente_url FROM moto_especificaciones WHERE moto_id=:id ORDER BY orden,id');
        $consulta->execute(['id' => $id]);
        return $consulta->fetchAll();
    }

    public function marcas(): array {
        return $this->conexion->query('SELECT id,nombre FROM marcas WHERE estado="Activo" ORDER BY nombre')->fetchAll();
    }

    public function marcaActiva(int $id): bool
    {
        $consulta = $this->conexion->prepare('SELECT 1 FROM marcas WHERE id=:id AND estado="Activo"');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return (bool)$consulta->fetchColumn();
    }

    public function guardar(array $datos): int
    {
        $this->conexion->beginTransaction();
        try {
            $consulta = $this->conexion->prepare('INSERT INTO motos(marca_id,modelo,cilindrada,color,precio,descripcion,anio,imagen,estado) VALUES(:marca,:modelo,:cc,:color,:precio,:descripcion,:anio,:imagen,"Activo")');
            $consulta->bindValue(':marca', (int)$datos['marca_id'], PDO::PARAM_INT);
            $consulta->bindValue(':modelo', (string)$datos['modelo'], PDO::PARAM_STR);
            $consulta->bindValue(':cc', (int)$datos['cilindrada'], PDO::PARAM_INT);
            $consulta->bindValue(':color', (string)$datos['color'], PDO::PARAM_STR);
            $consulta->bindValue(':precio', (string)$datos['precio'], PDO::PARAM_STR);
            $consulta->bindValue(':descripcion', (string)$datos['descripcion'], PDO::PARAM_STR);
            $consulta->bindValue(':anio', (int)$datos['anio'], PDO::PARAM_INT);
            $consulta->bindValue(':imagen', (string)$datos['imagen'], PDO::PARAM_STR);
            $consulta->execute();
            $id = (int)$this->conexion->lastInsertId();
            $unidad = $this->conexion->prepare('INSERT INTO unidades_moto(moto_id,codigo_serie,estado) VALUES(:moto,:serie,"Disponible")');
            $unidad->bindValue(':moto', $id, PDO::PARAM_INT);
            for ($i = 1; $i <= (int)$datos['cantidad']; $i++) {
                $unidad->bindValue(':serie', 'NC-' . $id . '-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT), PDO::PARAM_STR);
                $unidad->execute();
            }
            $this->conexion->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): void
    {
        $consulta = $this->conexion->prepare('UPDATE motos SET marca_id=:marca,modelo=:modelo,cilindrada=:cc,color=:color,precio=:precio,descripcion=:descripcion,anio=:anio,imagen=:imagen WHERE id=:id');
        $consulta->bindValue(':marca', (int)$datos['marca_id'], PDO::PARAM_INT);
        $consulta->bindValue(':modelo', (string)$datos['modelo'], PDO::PARAM_STR);
        $consulta->bindValue(':cc', (int)$datos['cilindrada'], PDO::PARAM_INT);
        $consulta->bindValue(':color', (string)$datos['color'], PDO::PARAM_STR);
        $consulta->bindValue(':precio', (string)$datos['precio'], PDO::PARAM_STR);
        $consulta->bindValue(':descripcion', (string)$datos['descripcion'], PDO::PARAM_STR);
        $consulta->bindValue(':anio', (int)$datos['anio'], PDO::PARAM_INT);
        $consulta->bindValue(':imagen', (string)$datos['imagen'], PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    /** Guarda colores y varias tomas por color sin eliminar la galería existente. */
    public function guardarGaleriaColores(int $motoId, array $colores): void
    {
        if ($colores === []) {
            return;
        }

        $this->ejecutarEnTransaccion(function () use ($motoId, $colores): void {
            $consultaOrdenColor = $this->conexion->prepare(
                'SELECT COALESCE(MAX(orden), 0) FROM moto_colores WHERE moto_id = :moto_id'
            );
            $consultaOrdenColor->execute(['moto_id' => $motoId]);
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

            foreach ($colores as $color) {
                $ordenColor++;
                $guardarColor->execute([
                    'moto_id' => $motoId,
                    'nombre' => $color['nombre'],
                    'codigo_hex' => $color['codigo_hex'],
                    'orden' => $ordenColor,
                ]);
                $colorId = (int) $this->conexion->lastInsertId();

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
        });
    }

    public function desactivar(int $id): void {
        $consulta = $this->conexion->prepare('UPDATE motos SET estado="Inactivo" WHERE id=:id');
        $consulta->execute(['id' => $id]);
    }

    public function unidadesDisponibles(string $buscar = '', int $limite = 30): array
    {
        $limite = max(1, min(100, $limite));
        $consultaSQL = "SELECT u.id unidad_id,u.codigo_serie,m.id moto_id,m.modelo,m.precio,m.imagen,ma.nombre marca FROM unidades_moto u JOIN motos m ON m.id=u.moto_id JOIN marcas ma ON ma.id=m.marca_id WHERE u.estado='Disponible' AND m.estado='Activo' AND ma.estado='Activo'";
        $parametros = [];
        if ($buscar !== '') {
            $consultaSQL .= ' AND (ma.nombre LIKE :term1 OR m.modelo LIKE :term2 OR CONCAT(ma.nombre," ",m.modelo) LIKE :term3 OR u.codigo_serie LIKE :term4)';
            $terminoBusqueda = '%' . $buscar . '%';
            $parametros = ['term1' => $terminoBusqueda,
                'term2' => $terminoBusqueda,
                'term3' => $terminoBusqueda,
                'term4' => $terminoBusqueda];
        }
        $consultaSQL .= ' ORDER BY ma.nombre,m.modelo,u.codigo_serie LIMIT ' . $limite;
        $consulta = $this->conexion->prepare($consultaSQL);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    public function inventario(): array
    {
        return $this->conexion->query('SELECT u.id,u.codigo_serie,u.estado,m.modelo,m.imagen,ma.nombre marca FROM unidades_moto u JOIN motos m ON m.id=u.moto_id JOIN marcas ma ON ma.id=m.marca_id ORDER BY u.estado,ma.nombre,m.modelo,u.codigo_serie')->fetchAll();
    }

    public function registrarUnidad(int $idMoto, string $codigoSerie): void
    {
        $this->conexion->beginTransaction();
        try {
            $moto = $this->conexion->prepare('SELECT id FROM motos WHERE id=:id AND estado="Activo" FOR UPDATE');
            $moto->execute(['id' => $idMoto]);
            if (!$moto->fetchColumn()) throw new \DomainException('Selecciona una motocicleta activa.');
            $unidad = $this->conexion->prepare('INSERT INTO unidades_moto(moto_id,codigo_serie,estado) VALUES(:moto,:serie,"Disponible")');
            $unidad->execute(['moto' => $idMoto,
                    'serie' => $codigoSerie]);
            $this->conexion->commit();
        } catch (\PDOException $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            if ((string)$e->getCode() === '23000') throw new \DomainException('Ese código de serie ya está registrado.');
            throw $e;
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            throw $e;
        }
    }
}
