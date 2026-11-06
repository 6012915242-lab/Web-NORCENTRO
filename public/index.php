<?php

declare(strict_types=1);
// Front Controller: inicia seguridad, enruta acciones y renderiza la vista solicitada.
require_once dirname(__DIR__).'/autoload.php';
use App\AccesoDatos\AuditoriaDAO;
use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use App\AccesoDatos\UsuarioDAO;
use App\AccesoDatos\VentaDAO;
use App\Configuracion\BaseDatos;
use App\Configuracion\Herramientas;
use App\Controladores\PaginaControlador;
use App\Controladores\SesionControlador;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\AutorizacionRbacMiddleware;
use App\Middlewares\SeguridadMiddleware;
use App\Nucleo\DespachadorAcciones;
use App\Nucleo\MotorVistas;
use App\Servicios\InicioServicio;
use App\Servicios\SincronizacionComercialServicio;

$rutaSesion = dirname(__DIR__).'/storage/sessions';
SeguridadMiddleware::inicializarEntornoSeguro($rutaSesion);
$_GET = SeguridadMiddleware::sanearArregloEntrada($_GET);
foreach ($_GET as $valorConsulta) {
    if (is_array($valorConsulta)) {
        http_response_code(400);
        exit('Los parámetros de la página no son válidos.');
    }
}
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
try {
    $conexion = BaseDatos::conectar();
    $usuariosDAO = new UsuarioDAO($conexion);
    $motosDAO = new MotoDAO($conexion);
    $clientesDAO = new ClienteDAO($conexion);
    $ventasDAO = new VentaDAO($conexion);
    $ofertasDAO = new OfertaDAO($conexion);
    $resumenDAO = new PanelDAO($conexion);
    $pedidosDAO = new PedidoDAO($conexion);
    $sesionesDAO = new SesionDAO($conexion);
    $permisosDAO = new PermisoDAO($conexion);
    $auditoriaDAO = new AuditoriaDAO($conexion);
    $auditoria = new AuditoriaMiddleware($auditoriaDAO);
    $autorizacionRbac = new AutorizacionRbacMiddleware($permisosDAO, $auditoriaDAO);
    $usuarioActual = AutenticacionMiddleware::validarSesionPersistida(
        $usuarioActual,
        $sesionesDAO
    );
    $sincronizacion = new SincronizacionComercialServicio($ofertasDAO, $pedidosDAO);
    $sincronizacion->actualizar();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        SeguridadMiddleware::validarPeticionPostCsrf();
        $datosPost = SeguridadMiddleware::sanearArregloEntrada($_POST);
        $accion = (string) ($datosPost['action'] ?? '');
        if ($accion === 'iniciar_sesion') {
            (new SesionControlador($usuariosDAO, $sesionesDAO))->iniciarSesion($datosPost);
        }
        AutenticacionMiddleware::exigirSesionParaAccion($usuarioActual);
        $autorizacionRbac->verificarPermisoAccion($accion, $usuarioActual);
        $auditoria->registrar($usuarioActual, $accion, $pagina);
        $despachador = new DespachadorAcciones(
            $usuariosDAO,
            $motosDAO,
            $clientesDAO,
            $ventasDAO,
            $ofertasDAO,
            $pedidosDAO,
            $sesionesDAO
        );
        $despachador->despachar($accion, $datosPost, $_FILES, $usuarioActual);

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
    $paginaControlador = new PaginaControlador(
        $motosDAO,
        $clientesDAO,
        $ventasDAO,
        $ofertasDAO,
        $resumenDAO,
        $pedidosDAO
    );
    extract($paginaControlador->preparar(
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
echo (new MotorVistas(dirname(__DIR__).'/vistas'))->renderizar($pagina, get_defined_vars());
