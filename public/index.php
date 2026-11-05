<?php
declare(strict_types=1);
// Front Controller: inicia seguridad, enruta acciones y renderiza la vista solicitada.
require_once dirname(__DIR__) . '/autoload.php';
use App\Configuracion\BaseDatos;
use App\Configuracion\Herramientas;
use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\AuditoriaDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\PermisoDAO;
use App\AccesoDatos\SesionDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\UsuarioDAO;
use App\AccesoDatos\VentaDAO;
use App\Controladores\SesionControlador;
use App\Controladores\PaginaControlador;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\AutorizacionRbacMiddleware;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\SeguridadMiddleware;
use App\Nucleo\MotorVistas;
use App\Nucleo\DespachadorAcciones;
use App\Servicios\SincronizacionComercialServicio;
use App\Servicios\InicioServicio;
$rutaSesion = dirname(__DIR__) . '/storage/sessions';
SeguridadMiddleware::inicializarEntornoSeguro($rutaSesion);
$_GET = SeguridadMiddleware::sanearArregloEntrada($_GET);
$pagina = preg_replace('/[^a-z_]/', '', (string)($_GET['page'] ?? 'inicio')) ?: 'inicio';
$paginasPublicas = ['inicio',
    'catalogo',
    'ofertas',
    'contacto',
    'login',
    'moto',
    'compra',
    'compra_credito'];
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
        $accion = (string)($datosPost['action'] ?? '');
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
    if (!in_array($pagina, $paginasPublicas, true)) {
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
} catch (Throwable $e) {
    error_log((string)$e);
    $esErrorDeConexion = $e instanceof PDOException
        && str_contains($e->getMessage(), 'No se pudo conectar');
    $mensajeError = 'No se pudo completar la solicitud. Revisa los datos y vuelve a intentarlo.';
    if ($esErrorDeConexion) {
        $baseDatosNoDisponible = true;
        $mensajeError = 'MySQL no está disponible. Inicia MySQL en XAMPP; si es la primera instalación, importa sql/norcentro_web.sql.';
    }
}
// Contacto no depende de MySQL y la portada usa únicamente los datos iniciales
// del SQL de instalación cuando la base todavía no se ha iniciado.
if ($baseDatosNoDisponible && $pagina === 'contacto') {
    $mensajeError = '';
}
if ($baseDatosNoDisponible && $pagina === 'inicio') {
    $mensajeError = '';
    $motoDestacada = ['id' => 0,
        'marca' => 'Bajaj',
        'modelo' => 'Pulsar NS400Z',
        'cilindrada' => 349,
        'precio' => 19141,
        'anio' => 2026,
        'imagen' => 'img/motos/bajaj-ns400z/rojo-negro-01.jpg'];
    $todasLasMotos = [
        $motoDestacada,
        ['id' => 0,
            'marca' => 'Yamaha',
            'modelo' => 'YZF-R15 ABS',
            'cilindrada' => 155,
            'precio' => 17337,
            'anio' => 2026,
            'imagen' => 'img/motos/yamaha-r15/azul-01.png',
            'descripcion' => 'Deportiva de 155 cc con VVA, ABS, control de tracción y conectividad Y-Connect.'],
    ];
    $ofertas = [[
            'id' => 0,
            'moto_id' => 0,
            'marca' => 'KTM',
            'modelo' => 'Duke 200',
            'imagen' => 'img/motos/ktm-duke200/gris-01.png',
            'cilindrada' => 199,
            'anio' => 2026,
            'titulo' => 'Oferta Norcentro',
            'descripcion' => 'Consulta en tienda la vigencia y disponibilidad de esta promoción.',
            'precio_normal' => 14000,
            'precio_oferta' => 12900,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => date('Y-m-d',
                strtotime('+7 days')),
        ]];
}
$motosDestacadas = [];
$ofertaDestacada = null;
if ($pagina === 'inicio') {
    $destacados = (new InicioServicio())->seleccionarDestacados(
        $todasLasMotos,
        $motoDestacada,
        $ofertas
    );
    $motosDestacadas = $destacados['motosDestacadas'];
    $ofertaDestacada = $destacados['ofertaDestacada'];
}
if (!isset($_SESSION['_flash'])) {
    $aviso = null;
} else {
    $aviso = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
}
$titulo = ['inicio' => 'Norcentro | Motocicletas',
    'catalogo' => 'Catálogo de motos',
    'ofertas' => 'Ofertas',
    'contacto' => 'Contacto | Norcentro',
    'login' => 'Iniciar sesión',
    'moto' => isset($motoDetalle)?$motoDetalle['marca'] . ' ' . $motoDetalle['modelo']:'Motocicleta',
    'compra' => 'Estado de compra | Norcentro',
    'compra_credito' => 'Compra a crédito | Norcentro',
    'dashboard' => 'Panel de gestión',
    'motos' => 'Motocicletas',
    'clientes' => 'Clientes',
    'inventario' => 'Inventario',
    'ventas' => 'Ventas',
    'creditos' => 'Créditos y cuotas'][$pagina] ?? 'Norcentro';
$vista = dirname(__DIR__) . '/vistas/paginas/' . $pagina . '.php';
if (!is_file($vista)) {
    http_response_code(404);
    $vista = dirname(__DIR__) . '/vistas/paginas/no_encontrado.php';
}
echo (new MotorVistas(dirname(__DIR__) . '/vistas'))->renderizar($pagina, get_defined_vars());
