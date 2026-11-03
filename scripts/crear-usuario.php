<?php
declare(strict_types = 1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/autoload.php';
use App\Configuracion\BaseDatos;
$nombreUsuario = trim((string)getenv('NORCENTRO_NEW_USERNAME'));
$clave = (string)getenv('NORCENTRO_NEW_PASSWORD');
$nombre = trim((string)getenv('NORCENTRO_NEW_NAME'));
$nombreRol = trim((string)(getenv('NORCENTRO_NEW_ROLE') ?: 'Administrador'));
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $nombreUsuario) || strlen($clave) < 12 || $nombre === '' || mb_strlen($nombre) > 120 || !in_array($nombreRol, ['Administrador',
        'Vendedor'], true)) {
    fwrite(STDERR, "Define un usuario válido, NORCENTRO_NEW_PASSWORD (mínimo 12 caracteres), NORCENTRO_NEW_NAME y NORCENTRO_NEW_ROLE (Administrador o Vendedor).\n");
    exit(1);
}
try {
    $conexion = BaseDatos::conectar();
    $roleQuery = $conexion->prepare('SELECT id FROM roles WHERE nombre=:name LIMIT 1');
    $roleQuery->execute(['name' => $nombreRol]);
    $rol = $roleQuery->fetchColumn();
    if (!$rol) throw new RuntimeException('No existe el rol solicitado. Importa primero sql/norcentro_web.sql.');
    $insercion = $conexion->prepare('INSERT INTO usuarios(rol_id,usuario,password_hash,nombre) VALUES(:role,:username,:hash,:name)');
    $insercion->execute(['role' => (int)$rol,
            'username' => $nombreUsuario,
            'hash' => password_hash($clave,
            PASSWORD_DEFAULT),
            'name' => $nombre]);
    fwrite(STDOUT, "Cuenta de {$nombreRol} creada para {$nombreUsuario}.\n");
} catch (PDOException $e) {
    if ((string)$e->getCode() === '23000') {
        fwrite(STDERR, "Ese nombre de usuario ya existe. Elige otro; el script no modifica cuentas existentes.\n");
        exit(1);
    }
    fwrite(STDERR, "No se pudo crear la cuenta. Revisa la conexión con MySQL y vuelve a intentarlo.\n");
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
