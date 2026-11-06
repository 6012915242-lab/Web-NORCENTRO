<?php

declare(strict_types=1);

/*
 * Crea una cuenta de Administrador o Vendedor desde la consola.
 * Uso en Windows (PowerShell), desde la raíz del proyecto:
 *   $env:NORCENTRO_NEW_USERNAME="admin"; $env:NORCENTRO_NEW_PASSWORD="ClaveSegura123";
 *   $env:NORCENTRO_NEW_NAME="Administrador"; $env:NORCENTRO_NEW_ROLE="Administrador";
 *   C:\xampp\php\php.exe scripts\crear-usuario.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__).'/autoload.php';

use App\AccesoDatos\UsuarioDAO;
use App\Configuracion\BaseDatos;

$nombreUsuario = trim((string) getenv('NORCENTRO_NEW_USERNAME'));
$clave = (string) getenv('NORCENTRO_NEW_PASSWORD');
$nombre = trim((string) getenv('NORCENTRO_NEW_NAME'));
$nombreRol = trim((string) (getenv('NORCENTRO_NEW_ROLE') ?: 'Administrador'));
$datosValidos = preg_match('/^[A-Za-z0-9._-]{3,50}$/', $nombreUsuario) === 1
    && strlen($clave) >= 12
    && $nombre !== ''
    && mb_strlen($nombre) <= 120
    && in_array($nombreRol, ['Administrador', 'Vendedor'], true);
if (! $datosValidos) {
    fwrite(STDERR, "Define un usuario válido, NORCENTRO_NEW_PASSWORD (mínimo 12 caracteres), NORCENTRO_NEW_NAME y NORCENTRO_NEW_ROLE (Administrador o Vendedor).\n");
    exit(1);
}
try {
    $usuarioDAO = new UsuarioDAO(BaseDatos::conectar());
    $rolId = $usuarioDAO->idRolPorNombre($nombreRol);

    if ($rolId === null) {
        throw new RuntimeException(
            'No existe el rol solicitado. Ejecuta primero scripts/migrar.php.'
        );
    }

    $usuarioDAO->crear(
        $rolId,
        $nombreUsuario,
        password_hash($clave, PASSWORD_DEFAULT),
        $nombre
    );

    fwrite(STDOUT, "Cuenta de {$nombreRol} creada para {$nombreUsuario}.\n");
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        fwrite(STDERR, "Ese nombre de usuario ya existe. Elige otro; el script no modifica cuentas existentes.\n");
        exit(1);
    }
    fwrite(STDERR, "No se pudo crear la cuenta. Revisa la conexión con MySQL y vuelve a intentarlo.\n");
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
}
