<?php
declare(strict_types = 1);
namespace App\Controladores;
use App\AccesoDatos\UsuarioDAO;
use App\Configuracion\Herramientas;
/** Valida el acceso y coordina el inicio y cierre de sesión. */
final class SesionControlador
{
    public function __construct(private UsuarioDAO $dao) {}

    public function iniciarSesion(array $post): never
    {
        $usuario = trim((string)($post['usuario'] ?? ''));
        $clave = (string)($post['password'] ?? '');
        $fila = $usuario !== ''?$this->dao->buscarActivo($usuario):null;
        if (!$fila || !password_verify($clave, $fila['password_hash'])) {
            Herramientas::mostrarAviso('error', 'Usuario o contraseña incorrectos.');
            Herramientas::redirigir('login');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$fila['id'],
            'nombre' => $fila['nombre'],
            'usuario' => $fila['usuario'],
            'rol' => $fila['rol']];
        Herramientas::redirigir('dashboard');
    }

    public function cerrarSesion(): never {
        $_SESSION = [];
        session_regenerate_id(true);
        Herramientas::mostrarAviso('success', 'Sesión cerrada correctamente.');
        Herramientas::redirigir('login');
    }
}
