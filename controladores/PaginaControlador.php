<?php
declare(strict_types=1);

namespace App\Controladores;

use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use App\AccesoDatos\PanelDAO;
use App\AccesoDatos\PedidoDAO;
use App\AccesoDatos\VentaDAO;

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
    ) {
    }

    public function preparar(string $pagina, array $consulta, ?array $usuarioActual): array
    {
        $datos = [
            'ofertas' => [],
            'todasLasMotos' => [],
            'motoDestacada' => null,
            'solicitudesCreditoPendientes' => 0,
        ];

        if ($usuarioActual !== null) {
            $datos['solicitudesCreditoPendientes'] =
                $this->pedidoDAO->contarSolicitudesCreditoPendientes();
        }

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
            $datos = array_replace($datos, $this->prepararOfertas($usuarioActual));
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
            $datos['elementos'] = $this->motoDAO->inventario();
            $datos['motosInventario'] = ($usuarioActual['rol'] ?? '') === 'Administrador'
                ? $this->motoDAO->listar()
                : [];
        }

        if ($pagina === 'ventas') {
            $datos = array_replace($datos, $this->prepararVentas($consulta));
        }

        if ($pagina === 'creditos') {
            $datos = array_replace($datos, $this->prepararCreditos($consulta));
        }

        return $datos;
    }

    private function prepararCatalogo(string $pagina, array $consulta): array
    {
        $ofertas = $this->ofertaDAO->activas();
        $todasLasMotos = $this->motoDAO->listar();
        $marcaSeleccionada = (int) ($consulta['marca'] ?? 0);
        $modeloSeleccionado = trim((string) ($consulta['modelo'] ?? ''));
        $cilindradaSeleccionada = (int) ($consulta['cilindrada'] ?? 0);
        $precioMaximo = (float) ($consulta['precio_max'] ?? 0);
        $marcasCatalogo = [];
        $modelosCatalogo = [];
        $cilindradasCatalogo = [];

        foreach ($todasLasMotos as $moto) {
            $marcasCatalogo[(int) $moto['marca_id']] = $moto['marca'];

            if ($marcaSeleccionada === 0 || (int) $moto['marca_id'] === $marcaSeleccionada) {
                $modelosCatalogo[$moto['modelo']] = $moto['modelo'];
                $cilindradasCatalogo[(int) $moto['cilindrada']] = (int) $moto['cilindrada'];
            }
        }

        if ($modeloSeleccionado !== '' && !isset($modelosCatalogo[$modeloSeleccionado])) {
            $modeloSeleccionado = '';
        }

        asort($marcasCatalogo);
        ksort($modelosCatalogo);
        sort($cilindradasCatalogo);

        $motoDestacada = null;
        foreach ($todasLasMotos as $moto) {
            if ($moto['modelo'] === 'Pulsar NS400Z') {
                $motoDestacada = $moto;
                break;
            }
        }

        $catalogo = $pagina === 'inicio'
            ? []
            : array_values(array_filter(
                $todasLasMotos,
                static function (array $moto) use (
                    $marcaSeleccionada,
                    $modeloSeleccionado,
                    $cilindradaSeleccionada,
                    $precioMaximo
                ): bool {
                    return ($marcaSeleccionada === 0 || (int) $moto['marca_id'] === $marcaSeleccionada)
                        && ($modeloSeleccionado === '' || $moto['modelo'] === $modeloSeleccionado)
                        && ($cilindradaSeleccionada === 0 || (int) $moto['cilindrada'] === $cilindradaSeleccionada)
                        && ($precioMaximo <= 0 || (float) $moto['precio'] <= $precioMaximo);
                }
            ));

        $variantesColorCatalogo = [];
        foreach ($catalogo as $moto) {
            $variantesColorCatalogo[(int) $moto['id']] =
                $this->motoDAO->coloresPublicos((int) $moto['id']);
        }

        return compact(
            'ofertas',
            'todasLasMotos',
            'motoDestacada',
            'marcasCatalogo',
            'modelosCatalogo',
            'cilindradasCatalogo',
            'marcaSeleccionada',
            'modeloSeleccionado',
            'cilindradaSeleccionada',
            'precioMaximo',
            'catalogo',
            'variantesColorCatalogo'
        );
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
                'texto_alt' => $motoDetalle['marca'] . ' ' . $motoDetalle['modelo'],
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

        return ['idSolicitud' => $requestId, 'pedidoCompra' => $pedidoCompra];
    }

    private function prepararOfertas(?array $usuarioActual): array
    {
        $datos = ['ofertas' => $this->ofertaDAO->activas()];

        if (($usuarioActual['rol'] ?? '') === 'Administrador') {
            $datos['listaOfertas'] = $this->ofertaDAO->listar();
            $datos['motosOferta'] = $this->motoDAO->listar();
        }

        return $datos;
    }

    private function prepararMotos(array $consulta): array
    {
        $editMoto = isset($consulta['edit'])
            ? $this->motoDAO->obtener((int) $consulta['edit'])
            : null;

        return [
            'elementos' => $this->motoDAO->listar(trim((string) ($consulta['q'] ?? ''))),
            'marcas' => $this->motoDAO->marcas(),
            'editMoto' => $editMoto,
            'coloresEdicion' => $editMoto !== null
                ? $this->motoDAO->coloresPublicos((int) $editMoto['id'])
                : [],
        ];
    }

    private function prepararClientes(array $consulta): array
    {
        return [
            'elementos' => $this->clienteDAO->listar(trim((string) ($consulta['q'] ?? ''))),
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
        $listaUnidades = $this->motoDAO->unidadesDisponibles($busquedaMoto, 30);
        $idSolicitudCreditoVenta = (int) ($consulta['pedido_id'] ?? 0);
        $idClienteVentaSeleccionado = $idSolicitudCreditoVenta > 0
            ? ($this->pedidoDAO->clienteAprobadoPorPedido($idSolicitudCreditoVenta) ?? 0)
            : (int) ($consulta['cliente_id'] ?? 0);
        $clienteVentaSeleccionado = $idClienteVentaSeleccionado > 0
            ? $this->clienteDAO->obtenerActivo($idClienteVentaSeleccionado)
            : null;

        if ($clienteVentaSeleccionado === null) {
            $idClienteVentaSeleccionado = 0;
        } elseif (!array_filter(
            $listaClientes,
            static fn (array $cliente): bool =>
                (int) $cliente['id'] === $idClienteVentaSeleccionado
        )) {
            array_unshift($listaClientes, $clienteVentaSeleccionado);
        }

        return [
            'elementos' => $this->ventaDAO->listar(),
            'busquedaClientes' => $busquedaClientes,
            'busquedaMoto' => $busquedaMoto,
            'listaClientes' => $listaClientes,
            'listaUnidades' => $listaUnidades,
            'idSolicitudCreditoVenta' => $idSolicitudCreditoVenta,
            'idClienteVentaSeleccionado' => $idClienteVentaSeleccionado,
            'clienteVentaSeleccionado' => $clienteVentaSeleccionado,
        ];
    }

    private function prepararCreditos(array $consulta): array
    {
        $creditos = $this->ventaDAO->creditos();
        $cuotas = [];
        $pagosCredito = [];

        foreach ($creditos as $credito) {
            $creditoId = (int) $credito['id'];
            $cuotas[$creditoId] = $this->ventaDAO->cuotas($creditoId);
            $pagosCredito[$creditoId] = $this->ventaDAO->pagosCredito($creditoId);
        }

        return [
            'creditos' => $creditos,
            'solicitudesCredito' => $this->pedidoDAO->solicitudesCredito(),
            'cuotas' => $cuotas,
            'pagosCredito' => $pagosCredito,
            'idVentaResaltada' => (int) ($consulta['venta_id'] ?? 0),
        ];
    }
}
