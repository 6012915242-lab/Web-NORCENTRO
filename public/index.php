<?php

declare(strict_types=1);

/*
 * FRONT CONTROLLER: punto de entrada único de la aplicación web.
 *
 * Ciclo de vida de cada petición:
 *   1. Seguridad inicial: sesión segura, cabeceras HTTP y saneamiento de $_GET.
 *   2. Conexión a la BD y validación de la sesión persistida.
 *   3. Petición POST: CSRF -> autenticación -> permiso RBAC -> auditoría -> controlador.
 *   4. Petición GET: protección de la ruta -> permiso RBAC -> el controlador prepara los datos.
 *   5. La vista se renderiza dentro del layout común (MotorVistas).
 */

require_once dirname(__DIR__).'/autoload.php';

use App\Configuracion\BaseDatos;
use App\Configuracion\Herramientas;
use App\Controladores\PaginaControlador;
use App\Controladores\SesionControlador;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\AutorizacionRbacMiddleware;
use App\Middlewares\SeguridadMiddleware;
use App\Nucleo\ContenedorDatos;
use App\Nucleo\DespachadorAcciones;
use App\Nucleo\MotorVistas;
use App\Servicios\InicioServicio;
use App\Servicios\SincronizacionComercialServicio;

// 1. Seguridad inicial ------------------------------------------------
$rutaSesion = dirname(__DIR__).'/storage/sessions';
SeguridadMiddleware::inicializarEntornoSeguro($rutaSesion);
$_GET = SeguridadMiddleware::sanearArregloEntrada($_GET);
foreach ($_GET as $valorConsulta) {
    if (is_array($valorConsulta)) {
        http_response_code(400);
        exit('Los parámetros de la página no son válidos.');
    }
}

// Solo se aceptan nombres de página con letras minúsculas y guion bajo.
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

// Valores por defecto para que la vista funcione incluso si la BD no responde.
$usuarioActual = $_SESSION['user'] ?? null;
$mensajeError = '';
$baseDatosNoDisponible = false;
$ofertas = [];
$todasLasMotos = [];
$motoDestacada = null;
$solicitudesCreditoPendientes = 0;

try {
    // 2. Conexión y sesión ---------------------------------------------

    $conexion = BaseDatos::conectar();
    $datos = new ContenedorDatos($conexion);
    $auditoria = new AuditoriaMiddleware($datos->auditoria);
    $autorizacionRbac = new AutorizacionRbacMiddleware($datos->permisos, $datos->auditoria);
    $usuarioActual = AutenticacionMiddleware::validarSesionPersistida(
        $usuarioActual,
        $datos->sesiones
    );
    // Vence ofertas y libera pedidos expirados antes de mostrar datos.
    (new SincronizacionComercialServicio($datos->ofertas, $datos->pedidos))->actualizar();

    // 3. Petición POST: cadena de middlewares y luego el controlador ------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        SeguridadMiddleware::validarPeticionPostCsrf();
        $datosPost = SeguridadMiddleware::sanearArregloEntrada($_POST);
        $accion = (string) ($datosPost['action'] ?? '');
        // El inicio de sesión es la única acción permitida sin sesión previa.
        if ($accion === 'iniciar_sesion') {
            (new SesionControlador($datos->usuarios, $datos->sesiones))->iniciarSesion($datosPost);
        }
        AutenticacionMiddleware::exigirSesionParaAccion($usuarioActual);
        $autorizacionRbac->verificarPermisoAccion($accion, $usuarioActual);
        $auditoria->registrar($usuarioActual, $accion, $pagina);
        DespachadorAcciones::desdeContenedor($datos)->despachar($accion, $datosPost, $_FILES, $usuarioActual);

        // Patrón Post/Redirect/Get: evita reenviar el formulario al recargar.
        Herramientas::redirigir($pagina);
    }

    // 4. Petición GET: protección de la ruta ------------------------------
    if (! in_array($pagina, $paginasPublicas, true)) {
        AutenticacionMiddleware::protegerRutaPrivada($pagina, $usuarioActual);
    }
    $autorizacionRbac->verificarAccesoPagina($pagina, $usuarioActual);
    $auditoria->registrar($usuarioActual, 'visita', $pagina);
    $permisosActuales = $usuarioActual !== null
        ? $autorizacionRbac->permisosDeUsuario((int) $usuarioActual['id'])
        : [];
    // Las vistas usan $usuarioPuede('permiso') para mostrar u ocultar opciones.
    $usuarioPuede = static fn (string $codigoPermiso): bool => in_array(
        $codigoPermiso,
        $permisosActuales,
        true
    );

    // El controlador entrega un arreglo con las variables que usará la vista.
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
        $mensajeError = 'MySQL no está disponible. Inicia MySQL en XAMPP; si es la primera instalación, ejecuta las migraciones (ver README).';
    }
}

// 5. Datos comunes y renderizado ------------------------------------------

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
// Aviso de un solo uso (mensaje flash) guardado por la acción anterior.
$aviso = $_SESSION['_flash'] ?? null;
unset($_SESSION['_flash']);

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
