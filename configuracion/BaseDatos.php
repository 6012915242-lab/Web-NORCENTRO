<?php

declare(strict_types=1);

namespace App\Configuracion;

use PDO;
use PDOException;

/**
 * Crea una única conexión PDO para las consultas de la aplicación.
 * Valores por defecto: XAMPP local (MySQL/MariaDB en el puerto 4404, usuario root sin clave).
 * Se pueden sobrescribir con las variables de entorno NORCENTRO_DB_*.
 */
final class BaseDatos
{
    public static function conectar(): PDO
    {
        $servidor = getenv('NORCENTRO_DB_HOST') ?: '127.0.0.1';
        $puerto = getenv('NORCENTRO_DB_PORT') ?: '4404';
        $nombreBaseDatos = getenv('NORCENTRO_DB_NAME') ?: 'norcentro_web';
        $usuarioBaseDatos = getenv('NORCENTRO_DB_USER') ?: 'root';
        $claveBaseDatos = getenv('NORCENTRO_DB_PASS') !== false ? (string) getenv('NORCENTRO_DB_PASS') : '';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $servidor,
            $puerto,
            $nombreBaseDatos
        );

        try {
            return new PDO($dsn, $usuarioBaseDatos, $claveBaseDatos, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $error) {
            throw new PDOException(
                'No se pudo conectar con MySQL. Revisa el servidor, el puerto y los datos de acceso.',
                (int) $error->getCode(),
                $error
            );
        }
    }
}
