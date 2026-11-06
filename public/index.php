<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/autoload.php';

use App\Configuracion\BaseDatos;
use App\Configuracion\Herramientas;
use App\Controladores\PaginaControlador;
use App\Controladores\SesionControlador;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\AutorizacionRbacMiddleware;
use App\Middlewares\SeguridadMiddleware;
use App\Nucleo\DespachadorAcciones;
use App\Nucleo\ContenedorDatos;
use App\Nucleo\MotorVistas;
use App\Servicios\InicioServicio;
use App\Servicios\SincronizacionComercialServicio;

/** Inicia la sesión segura y valida los parámetros de la ruta. */
$rutaSesion = dirname(__DIR__).'/storage/sessions';
SeguridadMiddleware::inicializarEntornoSeguro($rutaSesion);
$_GET = SeguridadMiddleware::sanearArregloEntrada($_GET);
foreach ($_GET as $valorConsulta) {
    if (is_array($valorConsulta)) {
        http_response_code(400);
        exit('Los parámetros de la página no son válidos.');
    }
}
/** Identifica la página solicitada y las rutas públicas disponibles. */
$pagina = preg_replace('/[^a-z_]/', '', (string) ($_GET['page'] ?? 'inicio')) ?: 'inicio';
$paginasPublicas = [
    'inicio',
    'catalogo',
    'ofertas',
    'contacto',
    'login',
    'moto',
    'compra',
    'compra_credito',
];
$usuarioActual = $_SESSION['user'] ?? null;
$mensajeError = '';
$baseDatosNoDisponible = false;
$ofertas = [];
$todasLasMotos = [];
$motoDestacada = null;
$solicitudesCreditoPendientes = 0;
/** Prepara los controladores y comprueba la sesión persistida. */
try {
    $conexion = BaseDatos::conectar();
    $datos = new ContenedorDatos($conexion);
    $auditoria = new AuditoriaMiddleware($datos->auditoria);
    $autorizacionRbac = new AutorizacionRbacMiddleware($datos->permisos, $datos->auditoria);
    $usuarioActual = AutenticacionMiddleware::validarSesionPersistida(
        $usuarioActual,
        $datos->sesiones
    );
    (new SincronizacionComercialServicio($datos->ofertas, $datos->pedidos))->actualizar();
    /** Una petición POST ejecuta una acción después de validar su seguridad. */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        SeguridadMiddleware::validarPeticionPostCsrf();
        $datosPost = SeguridadMiddleware::sanearArregloEntrada($_POST);
        $accion = (string) ($datosPost['action'] ?? '');
        if ($accion === 'iniciar_sesion') {
            (new SesionControlador($datos->usuarios, $datos->sesiones))->iniciarSesion($datosPost);
        }
        AutenticacionMiddleware::exigirSesionParaAccion($usuarioActual);
        $autorizacionRbac->verificarPermisoAccion($accion, $usuarioActual);
        $auditoria->registrar($usuarioActual, $accion, $pagina);
        DespachadorAcciones::desdeContenedor($datos)->despachar($accion, $datosPost, $_FILES, $usuarioActual);

        Herramientas::redirigir($pagina);
    }
    if (! in_array($pagina, $paginasPublicas, true)) {
        AutenticacionMiddleware::protegerRutaPrivada($pagina, $usuarioActual);
    }
    $autorizacionRbac->verificarAccesoPagina($pagina, $usuarioActual);
    $auditoria->registrar($usuarioActual, 'visita', $pagina);
    $permisosActuales = $usuarioActual !== null
        ? $autorizacionRbac->permisosDeUsuario((int) $usuarioActual['id'])
        : [];
    $usuarioPuede = static fn (string $codigoPermiso): bool => in_array(
        $codigoPermiso,
        $permisosActuales,
        true
    );
    /** El controlador prepara los datos que recibirá la vista. */
    extract(PaginaControlador::desdeContenedor($datos)->preparar(
        $pagina,
        $_GET,
        $usuarioActual,
        $permisosActuales
    ), EXTR_OVERWRITE);
} catch (Throwable $error) {
    error_log((string) $error);
    $esErrorDeConexion = $error instanceof PDOException
        && str_contains($error->getMessage(), 'No se pudo conectar');
    http_response_code($esErrorDeConexion ? 503 : 500);
    $mensajeError = 'No se pudo completar la solicitud. Revisa los datos y vuelve a intentarlo.';
    if ($esErrorDeConexion) {
        $baseDatosNoDisponible = true;
        $mensajeError = 'MySQL no está disponible. Inicia MySQL en XAMPP; si es la primera instalación, importa sql/norcentro_web.sql.';
    }
}
// La página de contacto puede mostrarse aunque MySQL no esté disponible.
if ($baseDatosNoDisponible && $pagina === 'contacto') {
    $mensajeError = '';
    http_response_code(200);
}
$motosDestacadas = [];
$ofertaDestacada = null;
if ($pagina === 'inicio') {
    $destacados = (new InicioServicio)->seleccionarDestacados(
        $todasLasMotos,
        $motoDestacada,
        $ofertas
    );
    $motosDestacadas = $destacados['motosDestacadas'];
    $ofertaDestacada = $destacados['ofertaDestacada'];
}
if (! isset($_SESSION['_flash'])) {
    $aviso = null;
} else {
    $aviso = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
}
$titulosPaginas = [
    'inicio' => 'Norcentro | Motocicletas',
    'catalogo' => 'Catálogo de motos',
    'ofertas' => 'Ofertas',
    'contacto' => 'Contacto | Norcentro',
    'login' => 'Iniciar sesión',
    'moto' => isset($motoDetalle) ? $motoDetalle['marca'].' '.$motoDetalle['modelo'] : 'Motocicleta',
    'compra' => 'Estado de compra | Norcentro',
    'compra_credito' => 'Compra a crédito | Norcentro',
    'dashboard' => 'Panel de gestión',
    'motos' => 'Motocicletas',
    'clientes' => 'Clientes',
    'inventario' => 'Inventario',
    'ventas' => 'Ventas',
    'creditos' => 'Créditos y cuotas',
];
$titulo = $titulosPaginas[$pagina] ?? 'Norcentro';
/** Renderiza la página con los datos preparados durante la petición. */
echo (new MotorVistas(dirname(__DIR__).'/vistas'))->renderizar($pagina, get_defined_vars());
