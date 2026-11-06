<?php

declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\VentaDAO;
use App\Nucleo\ContenedorDatos;
use App\Servicios\CatalogoFiltrosServicio;
use App\Servicios\OfertasCatalogoServicio;
use App\Servicios\ResumenCreditosServicio;

/** Prepara los datos de cada vista sin mezclar consultas en el Front Controller. */
final class PaginaControlador
{
    public function __construct(
        private MotoDAO $motoDAO,
        private ClienteDAO $clienteDAO,
        private VentaDAO $ventaDAO,
        private OfertaDAO $ofertaDAO,
        private PanelDAO $panelDAO,
        private PedidoDAO $pedidoDAO
    ) {}

    /** Construye el controlador con los DAO que comparte la petición. */
    public static function desdeContenedor(ContenedorDatos $datos): self
    {
        return new self(
            $datos->motos,
            $datos->clientes,
            $datos->ventas,
            $datos->ofertas,
            $datos->panel,
            $datos->pedidos
        );
    }

    public function preparar(
        string $pagina,
        array $consulta,
        ?array $usuarioActual,
        array $permisosUsuario = []
    ): array {
        $datos = [
            'ofertas' => [],
            'todasLasMotos' => [],
            'motoDestacada' => null,
            'solicitudesCreditoPendientes' => 0,
        ];

        if ($usuarioActual !== null && in_array('creditos.revisar', $permisosUsuario, true)) {
            $datos['solicitudesCreditoPendientes'] =
                $this->pedidoDAO->contarSolicitudesCreditoPendientes();
        }

        /** Inicio y catálogo comparten las consultas de motos y ofertas. */
        if (in_array($pagina, ['inicio', 'catalogo'], true)) {
            $datos = array_replace($datos, $this->prepararCatalogo($pagina, $consulta));
        }

        if ($pagina === 'moto') {
            $datos = array_replace(
                $datos,
                $this->prepararDetalleMoto((int) ($consulta['id'] ?? 0), $consulta)
            );
        }

        if ($pagina === 'compra') {
            $datos = array_replace(
                $datos,
                $this->prepararCompra((string) ($consulta['request_id'] ?? ''))
            );
        }

        if ($pagina === 'ofertas') {
            $datos = array_replace($datos, $this->prepararOfertas($permisosUsuario));
        }

        if ($pagina === 'dashboard') {
            $datos['resumen'] = $this->panelDAO->resumen();
        }

        if ($pagina === 'motos') {
            $datos = array_replace($datos, $this->prepararMotos($consulta));
        }

        if ($pagina === 'clientes') {
            $datos = array_replace($datos, $this->prepararClientes($consulta));
        }

        if ($pagina === 'inventario') {
            $datos['busquedaInventario'] = mb_substr(trim((string) ($consulta['inventario_q'] ?? '')), 0, 100);
            $estadoInventario = (string) ($consulta['estado_inventario'] ?? '');
            $datos['estadoInventario'] = in_array($estadoInventario, ['Disponible', 'Reservada', 'Vendida'], true)
                ? $estadoInventario
                : '';
            $datos['elementos'] = $this->motoDAO->inventario(
                $datos['busquedaInventario'],
                $datos['estadoInventario']
            );
            $puedeGestionar = in_array('inventario.gestionar', $permisosUsuario, true);
            $datos['coloresInventario'] = $puedeGestionar ? $this->motoDAO->coloresParaInventario() : [];
            $datos['stockPorColor'] = $this->motoDAO->stockPorColor();
        }

        if ($pagina === 'ventas') {
            $datos = array_replace($datos, $this->prepararVentas($consulta));
        }

        if ($pagina === 'creditos') {
            $datos = array_replace($datos, $this->prepararCreditos($consulta));
        }

        return $datos;
    }

    /** Prepara las motos, ofertas y filtros que utilizan inicio y catálogo. */
    private function prepararCatalogo(string $pagina, array $consulta): array
    {
        $ofertas = $this->ofertaDAO->activas();
        $todasLasMotos = $this->motoDAO->listar();
        $filtros = (new CatalogoFiltrosServicio)->preparar(
            $todasLasMotos,
            $consulta,
            $pagina !== 'inicio'
        );
        $variantes = $this->motoDAO->coloresPorMotos(array_column($filtros['catalogo'], 'id'));

        return array_merge($filtros, [
            'ofertas' => $ofertas,
            'ofertasPorMoto' => (new OfertasCatalogoServicio)->agruparPorMoto($ofertas),
            'todasLasMotos' => $todasLasMotos,
            'variantesColorCatalogo' => $variantes,
        ]);
    }

    private function prepararDetalleMoto(int $motoId, array $consulta): array
    {
        $motoDetalle = $this->motoDAO->detallePublico($motoId);

        if ($motoDetalle === null) {
            http_response_code(404);

            return ['motoDetalle' => null];
        }

        $ofertaMotoDetalle = $this->ofertaDAO->activaParaMoto((int) $motoDetalle['id']);
        $variantesColor = $this->motoDAO->coloresPublicos((int) $motoDetalle['id']);
        $idColorSeleccionado = (int) ($consulta['color'] ?? 0);
        $colorSeleccionado = null;

        foreach ($variantesColor as $variante) {
            if ((int) $variante['id'] === $idColorSeleccionado) {
                $colorSeleccionado = $variante;
                break;
            }
        }

        $colorSeleccionado ??= $variantesColor[0] ?? null;
        $imagenesMoto = $colorSeleccionado !== null
            ? $colorSeleccionado['imagenes']
            : $this->motoDAO->imagenesPublicas((int) $motoDetalle['id']);

        if ($imagenesMoto === []) {
            $imagenesMoto = [[
                'ruta' => $motoDetalle['imagen'],
                'texto_alt' => $motoDetalle['marca'].' '.$motoDetalle['modelo'],
            ]];
        }

        $especificacionesMoto =
            $this->motoDAO->especificacionesPublicas((int) $motoDetalle['id']);

        return compact(
            'motoDetalle',
            'ofertaMotoDetalle',
            'variantesColor',
            'colorSeleccionado',
            'imagenesMoto',
            'especificacionesMoto'
        );
    }

    private function prepararCompra(string $requestId): array
    {
        $requestValido = preg_match('/^[0-9a-f-]{36}$/i', $requestId) === 1;
        $pedidoCompra = $requestValido ? $this->pedidoDAO->obtenerPorRequest($requestId) : null;

        if ($pedidoCompra === null) {
            http_response_code(404);
        }

        return [
            'idSolicitud' => $requestId,
            'pedidoCompra' => $pedidoCompra,
        ];
    }

    private function prepararOfertas(array $permisosUsuario): array
    {
        $datos = ['ofertas' => $this->ofertaDAO->activas()];

        if (in_array('ofertas.gestionar', $permisosUsuario, true)) {
            $datos['listaOfertas'] = $this->ofertaDAO->listar();
            $datos['motosOferta'] = $this->motoDAO->listar();
        }

        return $datos;
    }

    private function prepararMotos(array $consulta): array
    {
        $busqueda = mb_substr(trim((string) ($consulta['q'] ?? '')), 0, 100);
        $editMoto = isset($consulta['edit'])
            ? $this->motoDAO->obtener((int) $consulta['edit'])
            : null;

        return [
            'elementos' => $this->motoDAO->listar($busqueda),
            'busquedaMotos' => $busqueda,
            'marcas' => $this->motoDAO->marcas(),
            'editMoto' => $editMoto,
            'coloresEdicion' => $editMoto !== null
                ? $this->motoDAO->coloresPublicos((int) $editMoto['id'])
                : [],
            'disponiblesPorColor' => $editMoto !== null
                ? $this->motoDAO->disponiblesPorColor((int) $editMoto['id'])
                : [],
        ];
    }

    private function prepararClientes(array $consulta): array
    {
        $busqueda = mb_substr(trim((string) ($consulta['q'] ?? '')), 0, 100);

        return [
            'elementos' => $this->clienteDAO->listar($busqueda),
            'busquedaClientes' => $busqueda,
            'continuarVenta' => ($consulta['next'] ?? '') === 'venta',
            'idClienteRegistrado' => (int) ($consulta['cliente_registrado'] ?? 0),
            'editCliente' => isset($consulta['edit'])
                ? $this->clienteDAO->obtener((int) $consulta['edit'])
                : null,
        ];
    }

    private function prepararVentas(array $consulta): array
    {
        $busquedaClientes = mb_substr(trim((string) ($consulta['cliente_q'] ?? '')), 0, 100);
        $busquedaMoto = mb_substr(trim((string) ($consulta['moto_q'] ?? '')), 0, 100);
        $listaClientes = $this->clienteDAO->buscarActivos($busquedaClientes, 20);
        $listaUnidades = $this->motoDAO->unidadesDisponibles($busquedaMoto, 100);
        $idSolicitudCreditoVenta = (int) ($consulta['pedido_id'] ?? 0);
        $idClienteVentaSeleccionado = $idSolicitudCreditoVenta > 0
            ? ($this->pedidoDAO->clienteAprobadoPorPedido($idSolicitudCreditoVenta) ?? 0)
            : (int) ($consulta['cliente_id'] ?? 0);
        $clienteVentaSeleccionado = $idClienteVentaSeleccionado > 0
            ? $this->clienteDAO->obtenerActivo($idClienteVentaSeleccionado)
            : null;

        if ($clienteVentaSeleccionado === null) {
            $idClienteVentaSeleccionado = 0;
        } elseif (! array_filter(
            $listaClientes,
            static fn (array $cliente): bool => (int) $cliente['id'] === $idClienteVentaSeleccionado
        )) {
            array_unshift($listaClientes, $clienteVentaSeleccionado);
        }

        return [
            'elementos' => $this->ventaDAO->listar(),
            'busquedaClientes' => $busquedaClientes,
            'busquedaMoto' => $busquedaMoto,
            'listaClientes' => $listaClientes,
            'listaUnidades' => $listaUnidades,
            'unidadesPorModelo' => $this->agruparUnidadesPorModelo($listaUnidades),
            'idSolicitudCreditoVenta' => $idSolicitudCreditoVenta,
            'idClienteVentaSeleccionado' => $idClienteVentaSeleccionado,
            'clienteVentaSeleccionado' => $clienteVentaSeleccionado,
        ];
    }

    private function prepararCreditos(array $consulta): array
    {
        $busquedaCredito = mb_substr(trim((string) ($consulta['buscar_credito'] ?? '')), 0, 100);
        $todosLosCreditos = $this->ventaDAO->creditos();
        $creditos = $busquedaCredito === ''
            ? $todosLosCreditos
            : $this->ventaDAO->creditos($busquedaCredito);
        $detalles = $this->ventaDAO->detallesCreditos(array_column($todosLosCreditos, 'id'));
        $cuotas = $detalles['cuotas'];
        $pagosCredito = $detalles['pagos'];

        $solicitudes = $this->pedidoDAO->solicitudesCredito();
        $resumen = (new ResumenCreditosServicio)->calcular($todosLosCreditos, $cuotas, $solicitudes);

        return $resumen + [
            'creditos' => $creditos,
            'busquedaCredito' => $busquedaCredito,
            'solicitudesCredito' => $solicitudes,
            'cuotas' => $cuotas,
            'pagosCredito' => $pagosCredito,
            'idVentaResaltada' => (int) ($consulta['venta_id'] ?? 0),
        ];
    }

    /**
     * Agrupa las unidades disponibles por modelo y color para el selector de ventas:
     * cada combinación aparece una vez y debajo se listan sus números de serie.
     */
    private function agruparUnidadesPorModelo(array $unidades): array
    {
        $grupos = [];
        foreach ($unidades as $unidad) {
            $clave = (int) $unidad['moto_id'].':'.(int) ($unidad['color_id'] ?? 0);
            $grupos[$clave] ??= [
                'nombre' => $unidad['marca'].' '.$unidad['modelo'],
                'color' => $unidad['color'] ?? 'Color por asignar',
                'precio' => (float) $unidad['precio'],
                'unidades' => [],
            ];
            $grupos[$clave]['unidades'][] = $unidad;
        }

        return array_values($grupos);
    }
}
