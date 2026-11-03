<?php
declare(strict_types = 1);
namespace App\AccesoDatos;
use PDO;
/** Busca usuarios y registra credenciales y roles para el acceso. */
final class UsuarioDAO
{
    public function __construct(private PDO $conexion) {}

    public function buscarActivo(string $usuario): ?array
    {
        $consulta = $this->conexion->prepare('SELECT u.id, u.usuario, u.password_hash, u.nombre, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.usuario=:usuario AND u.estado="Activo" LIMIT 1');
        $consulta->execute(['usuario' => $usuario]);
        return $consulta->fetch() ?: null;
    }
}
