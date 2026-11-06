<?php

declare(strict_types=1);

use App\Configuracion\BaseDatos;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require_once dirname(__DIR__).'/autoload.php';

try {
    $conexion = BaseDatos::conectar();
    $conexion->exec('CREATE TABLE IF NOT EXISTS migraciones (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        archivo VARCHAR(255) NOT NULL UNIQUE,
        ejecutado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    $archivos = glob(dirname(__DIR__).'/migraciones/[0-9][0-9][0-9]_*.sql') ?: [];
    sort($archivos, SORT_STRING);
    $registrada = $conexion->prepare('SELECT COUNT(*) FROM migraciones WHERE archivo = :archivo');
    $registrar = $conexion->prepare('INSERT INTO migraciones (archivo) VALUES (:archivo)');

    foreach ($archivos as $archivo) {
        $nombre = basename($archivo);
        $registrada->execute(['archivo' => $nombre]);
        if ((int) $registrada->fetchColumn() > 0) {
            echo "[Aplicada] $nombre\n";
            continue;
        }
        if ($nombre === '001_esquema_inicial.sql') {
            $tablas = (int) $conexion->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name <> 'migraciones'")->fetchColumn();
            if ($tablas > 0) {
                throw new RuntimeException('La base de datos ya contiene tablas y no tiene registrada la migración inicial. No se importará el esquema automáticamente.');
            }
            $archivo = dirname(__DIR__).'/sql/norcentro_web.sql';
        }
        $sql = file_get_contents($archivo);
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer la migración '.$nombre);
        }
        $conexion->exec($sql);
        $registrar->execute(['archivo' => $nombre]);
        echo "[Ejecutada] $nombre\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Error al migrar: '.$error->getMessage()."\n");
    exit(1);
}
