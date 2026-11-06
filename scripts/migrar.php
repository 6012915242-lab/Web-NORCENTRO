<?php

declare(strict_types=1);

/*
 * Ejecuta las migraciones pendientes de la carpeta migraciones/ en orden numérico.
 * Uso (desde la raíz del proyecto):  C:\xampp\php\php.exe scripts\migrar.php
 *
 * Cada migración ejecutada se registra en la tabla `migraciones`, así que el
 * script puede ejecutarse varias veces sin repetir cambios.
 */

use App\Configuracion\BaseDatos;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require_once dirname(__DIR__).'/autoload.php';

const MIGRACION_INICIAL = '001_esquema_inicial.sql';

try {
    $conexion = BaseDatos::conectar();
    $conexion->exec('CREATE TABLE IF NOT EXISTS migraciones (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        archivo VARCHAR(255) NOT NULL UNIQUE,
        ejecutado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $consultaRegistrada = $conexion->prepare('SELECT COUNT(*) FROM migraciones WHERE archivo = :archivo');
    $consultaRegistrar = $conexion->prepare('INSERT INTO migraciones (archivo) VALUES (:archivo)');

    $archivos = glob(dirname(__DIR__).'/migraciones/[0-9][0-9][0-9]_*.sql') ?: [];
    sort($archivos, SORT_STRING);

    foreach ($archivos as $rutaArchivo) {
        $nombre = basename($rutaArchivo);

        $consultaRegistrada->execute(['archivo' => $nombre]);
        $yaAplicada = (int) $consultaRegistrada->fetchColumn() > 0;
        $consultaRegistrada->closeCursor();
        if ($yaAplicada) {
            echo "[Aplicada]  $nombre\n";
            continue;
        }

        // Si la base se instaló antes desde phpMyAdmin, el esquema inicial ya existe:
        // se registra como aplicado para no duplicar tablas ni datos.
        if ($nombre === MIGRACION_INICIAL && contarTablasExistentes($conexion) > 0) {
            $consultaRegistrar->execute(['archivo' => $nombre]);
            echo "[Registrada] $nombre (el esquema ya existía)\n";
            continue;
        }

        $sql = file_get_contents($rutaArchivo);
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer la migración '.$nombre);
        }

        $conexion->exec($sql);
        $consultaRegistrar->execute(['archivo' => $nombre]);
        echo "[Ejecutada] $nombre\n";
    }

    echo "Base de datos al día.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Error al migrar: '.$error->getMessage()."\n");
    exit(1);
}

/** Cuenta las tablas de la base actual sin considerar la tabla de control de migraciones. */
function contarTablasExistentes(PDO $conexion): int
{
    return (int) $conexion->query(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name <> 'migraciones'"
    )->fetchColumn();
}
