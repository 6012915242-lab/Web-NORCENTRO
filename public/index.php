<?php
declare(strict_types=1);
// Front Controller: inicia seguridad, enruta acciones y renderiza la vista solicitada.
require_once dirname(__DIR__) . '/autoload.php';
use App\Configuracion\BaseDatos;
use App\Configuracion\Herramientas;
use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\UsuarioDAO;
use App\AccesoDatos\VentaDAO;
use App\Controladores\SesionControlador;
use App\Controladores\ClienteControlador;
use App\Controladores\MotoControlador;
use App\Controladores\OfertaControlador;
use App\Controladores\PedidoControlador;
use App\Controladores\VentaControlador;
use App\Middlewares\AutenticacionMiddleware;
use App\Middlewares\AutorizacionRbacMiddleware;
use App\Middlewares\AuditoriaMiddleware;
use App\Middlewares\SeguridadMiddleware;
use App\Nucleo\MotorVistas;
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
    AuditoriaMiddleware::registrar(
        $conexion,
        $usuarioActual,
        (string)($_POST['action'] ?? 'visita'),
        $pagina
    );
    if ($usuarioActual && in_array($usuarioActual['rol'], ['Administrador',
            'Vendedor'], true)) $solicitudesCreditoPendientes = $pedidosDAO->contarSolicitudesCreditoPendientes();
    $ofertasDAO->actualizarEstadoFechas();
    $pedidosDAO->liberarPedidosVencidos();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        SeguridadMiddleware::validarPeticionPostCsrf();
        $datosPost = SeguridadMiddleware::sanearArregloEntrada($_POST);
        $accion = (string)($datosPost['action'] ?? '');
        if ($accion === 'iniciar_sesion') (new SesionControlador($usuariosDAO))->iniciarSesion($datosPost);
        AutenticacionMiddleware::exigirSesionParaAccion($usuarioActual);
        AutorizacionRbacMiddleware::verificarPermisoAccion($accion, $usuarioActual);
        switch ($accion) {
            case 'cerrar_sesion':
                (new SesionControlador($usuariosDAO))->cerrarSesion();
                break;

            case 'guardar_moto':
                (new MotoControlador($motosDAO))->guardar(
                    $datosPost,
                    $_FILES['imagen'] ?? null,
                    (int) ($datosPost['id'] ?? 0) > 0
                );
                break;

            case 'desactivar_moto':
                (new MotoControlador($motosDAO))->desactivar((int) ($datosPost['id'] ?? 0));
                break;

            case 'agregar_unidad_inventario':
                (new MotoControlador($motosDAO))->agregarUnidadInventario($datosPost);
                break;

            case 'guardar_cliente':
                (new ClienteControlador($clientesDAO))->guardar(
                    $datosPost,
                    (int) ($datosPost['id'] ?? 0) > 0
                );
                break;

            case 'desactivar_cliente':
                (new ClienteControlador($clientesDAO))->desactivar((int) ($datosPost['id'] ?? 0));
                break;

            case 'guardar_oferta':
                (new OfertaControlador($ofertasDAO))->guardar($datosPost);
                break;

            case 'desactivar_oferta':
                (new OfertaControlador($ofertasDAO))->desactivar((int) ($datosPost['id'] ?? 0));
                break;

            case 'crear_venta':
                (new VentaControlador($ventasDAO))->crear($datosPost, (int) $usuarioActual['id']);
                break;

            case 'registrar_pago_cuota':
                (new VentaControlador($ventasDAO))->pagar($datosPost);
                break;

            case 'evaluar_solicitud_credito':
                (new PedidoControlador($pedidosDAO))->evaluarCredito($datosPost);
                break;

            default:
                Herramientas::mostrarAviso('error', 'La acción solicitada no existe.');
                break;
        }

        Herramientas::redirigir($pagina);
    }
    if (!in_array($pagina, $paginasPublicas, true)) {
        AutenticacionMiddleware::protegerRutaPrivada($pagina, $usuarioActual);
    }
    AutorizacionRbacMiddleware::verificarAccesoPagina($pagina, $usuarioActual);
    if (in_array($pagina, ['inicio',
            'catalogo'], true)) {
        $ofertas = $ofertasDAO->activas();
        $todasLasMotos = $motosDAO->listar();
        $marcasCatalogo = [];
        $modelosCatalogo = [];
        $cilindradasCatalogo = [];
        $marcaSeleccionada = (int)($_GET['marca'] ?? 0);
        $modeloSeleccionado = trim((string)($_GET['modelo'] ?? ''));
        $cilindradaSeleccionada = (int)($_GET['cilindrada'] ?? 0);
        $precioMaximo = (float)($_GET['precio_max'] ?? 0);
        foreach ($todasLasMotos as $moto) {
            $marcasCatalogo[(int)$moto['marca_id']] = $moto['marca'];
            if (!$marcaSeleccionada || (int)$moto['marca_id'] === $marcaSeleccionada) {
                $modelosCatalogo[$moto['modelo']] = $moto['modelo'];
                $cilindradasCatalogo[(int)$moto['cilindrada']] = (int)$moto['cilindrada'];
            }
        }
        if ($modeloSeleccionado !== '' && !isset($modelosCatalogo[$modeloSeleccionado]))$modeloSeleccionado = '';
        asort($marcasCatalogo);
        ksort($modelosCatalogo);
        sort($cilindradasCatalogo);
        $motoDestacada = null;
        foreach ($todasLasMotos as $moto) if ($moto['modelo'] === 'Pulsar NS400Z') {
            $motoDestacada = $moto;
            break;
        }
        if ($pagina === 'inicio') {
            $catalogo = [];
        } else {
            $catalogo = array_values(array_filter($todasLasMotos, static fn($moto) => (!$marcaSeleccionada || (int)$moto['marca_id'] === $marcaSeleccionada) && (!$modeloSeleccionado || $moto['modelo'] === $modeloSeleccionado) && (!$cilindradaSeleccionada || (int)$moto['cilindrada'] === $cilindradaSeleccionada) && (!$precioMaximo || (float)$moto['precio'] <= $precioMaximo)));
        }
        $variantesColorCatalogo = [];
        foreach ($catalogo as $moto) $variantesColorCatalogo[(int)$moto['id']] = $motosDAO->coloresPublicos((int)$moto['id']);
    }
    if ($pagina === 'moto') {
        $motoDetalle = $motosDAO->detallePublico((int)($_GET['id'] ?? 0));
        if ($motoDetalle) {
            $ofertaMotoDetalle = $ofertasDAO->activaParaMoto((int)$motoDetalle['id']);
            $variantesColor = $motosDAO->coloresPublicos((int)$motoDetalle['id']);
            $idColorSeleccionado = (int)($_GET['color'] ?? 0);
            $colorSeleccionado = null;
            foreach ($variantesColor as $variante) if ((int)$variante['id'] === $idColorSeleccionado) $colorSeleccionado = $variante;
            $colorSeleccionado = $colorSeleccionado ?? ($variantesColor[0] ?? null);
            $imagenesMoto = $colorSeleccionado?$colorSeleccionado['imagenes']:$motosDAO->imagenesPublicas((int)$motoDetalle['id']);
            if (!$imagenesMoto)$imagenesMoto = [['ruta' => $motoDetalle['imagen'],
                    'texto_alt' => $motoDetalle['marca'] . ' ' . $motoDetalle['modelo']]];
            $especificacionesMoto = $motosDAO->especificacionesPublicas((int)$motoDetalle['id']);
        } else http_response_code(404);
    }
    if ($pagina === 'compra') {
        $idSolicitud = (string)($_GET['request_id'] ?? '');
        $pedidoCompra = preg_match('/^[0-9a-f-]{36}$/i', $idSolicitud)?$pedidosDAO->obtenerPorRequest($idSolicitud):null;
        if (!$pedidoCompra)http_response_code(404);
    }
    if ($pagina === 'ofertas') {
        $ofertas = $ofertasDAO->activas();
        if ($usuarioActual && $usuarioActual['rol'] === 'Administrador') {
            $listaOfertas = $ofertasDAO->listar();
            $motosOferta = $motosDAO->listar();
        }
    }
    if ($pagina === 'dashboard') $resumen = $resumenDAO->resumen();
    if ($pagina === 'motos') {
        $elementos = $motosDAO->listar(trim((string)($_GET['q'] ?? '')));
        $marcas = $motosDAO->marcas();
        $editMoto = isset($_GET['edit'])?$motosDAO->obtener((int)$_GET['edit']):null;
    }
    if ($pagina === 'clientes') {
        $elementos = $clientesDAO->listar(trim((string)($_GET['q'] ?? '')));
        $editCliente = isset($_GET['edit'])?$clientesDAO->obtener((int)$_GET['edit']):null;
    }
    if ($pagina === 'inventario') {
        $elementos = $motosDAO->inventario();
        $motosInventario = $usuarioActual['rol'] === 'Administrador'?$motosDAO->listar():[];
    }
    if ($pagina === 'ventas') {
        $elementos = $ventasDAO->listar();
        $busquedaClientes = mb_substr(trim((string)($_GET['cliente_q'] ?? '')), 0, 100);
        $busquedaMoto = mb_substr(trim((string)($_GET['moto_q'] ?? '')), 0, 100);
        $listaClientes = $clientesDAO->buscarActivos($busquedaClientes, 20);
        $listaUnidades = $motosDAO->unidadesDisponibles($busquedaMoto, 30);
        $idSolicitudCreditoVenta = (int)($_GET['pedido_id'] ?? 0);
        $idClienteVentaSeleccionado = $idSolicitudCreditoVenta > 0?($pedidosDAO->clienteAprobadoPorPedido($idSolicitudCreditoVenta) ?? 0):(int)($_GET['cliente_id'] ?? 0);
        $clienteVentaSeleccionado = $idClienteVentaSeleccionado > 0?$clientesDAO->obtenerActivo($idClienteVentaSeleccionado):null;
        if (!$clienteVentaSeleccionado)$idClienteVentaSeleccionado = 0;
        elseif (!array_filter($listaClientes, static fn($cliente) => (int)$cliente['id'] === $idClienteVentaSeleccionado))array_unshift($listaClientes, $clienteVentaSeleccionado);
    }
    if ($pagina === 'creditos') {
        $creditos = $ventasDAO->creditos();
        $solicitudesCredito = $pedidosDAO->solicitudesCredito();
        $cuotas = [];
        $pagosCredito = [];
        foreach ($creditos as $credito) {
            $cuotas[$credito['id']] = $ventasDAO->cuotas((int)$credito['id']);
            $pagosCredito[$credito['id']] = $ventasDAO->pagosCredito((int)$credito['id']);
        }
        $idVentaResaltada = (int)($_GET['venta_id'] ?? 0);
    }
} catch (Throwable $e) {
    error_log((string)$e);
    $mensajeError = $e instanceof PDOException ? $e->getMessage() : 'No se pudo completar la solicitud. Revisa los datos y vuelve a intentarlo.';
    if (str_contains($mensajeError, 'No se pudo conectar')) {
        $baseDatosNoDisponible = true;
        $mensajeError = 'MySQL no está disponible. Inicia MySQL en XAMPP; si es la primera instalación, importa sql/norcentro_web.sql.';
    }
}
// Contacto no depende de MySQL y la portada usa únicamente los datos iniciales
// del SQL de instalación cuando la base todavía no se ha iniciado.
if ($baseDatosNoDisponible && $pagina === 'contacto') $mensajeError = '';
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
    foreach (['YZF-R15 ABS',
            'MT-09 ABS',
            'Pulsar NS200',
            'Duke 200'] as $modeloDestacado) {
        foreach ($todasLasMotos as $candidato) {
            if ($candidato['modelo'] !== $modeloDestacado || (int)$candidato['id'] === (int)($motoDestacada['id'] ?? - 1)) continue;
            $motosDestacadas[] = $candidato;
            break 2;
        }
    }
    $mejorDescuento = - 1;
    foreach ($ofertas as $oferta) {
        $normal = (float)($oferta['precio_normal'] ?? 0);
        $especial = (float)($oferta['precio_oferta'] ?? 0);
        if ($normal <= 0 || $especial <= 0 || $especial >= $normal) continue;
        $descuento = ($normal - $especial) / $normal;
        if ($descuento > $mejorDescuento) {
            $mejorDescuento = $descuento;
            $ofertaDestacada = $oferta;
        }
    }
}
if (!isset($_SESSION['_flash'])) $aviso = null;
else {
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
