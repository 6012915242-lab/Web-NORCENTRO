<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Nucleo\ModeloBaseORM;
use PDO;

/** Persiste pedidos, reservas, pagos y solicitudes de crédito. */
final class PedidoDAO extends ModeloBaseORM
{
    public function crear(array $datos): array
    {
        $this->conexion->beginTransaction();
        try {
            $this->liberarPedidosVencidos();
            $existente = $this->conexion->prepare('SELECT * FROM pedidos WHERE request_id=:key FOR UPDATE');
            $existente->execute(['key' => $datos['request_id']]);
            $conocido = $existente->fetch();
            if ($conocido) {
                $elementos = $this->items((int) $conocido['id']);
                $this->verificarReintento($conocido, $elementos, $datos);
                $this->conexion->commit();

                return ['id' => (int) $conocido['id'],
                    'modalidad' => $conocido['modalidad'],
                    'estado' => $conocido['estado'],
                    'total' => (float) $conocido['total'],
                    'checkout_url' => $conocido['checkout_url'],
                    'mp_order_id' => $conocido['mp_order_id'],
                    'items' => $elementos,
                    'repetido' => true];
            }
            $lineas = [];
            $total = 0.0;
            $unidadesAsignadas = [];
            foreach ($datos['items'] as $elemento) {
                $moto = (int) $elemento['moto_id'];
                $cantidad = (int) $elemento['cantidad'];
                $color = $elemento['color_id'] ?: null;
                $consulta = $this->conexion->prepare('
                    SELECT m.id, m.modelo, ma.nombre marca, m.precio, m.imagen, m.estado
                    FROM motos m JOIN marcas ma ON ma.id = m.marca_id
                    WHERE m.id = :id AND m.estado = "Activo" AND ma.estado = "Activo"
                ');
                $consulta->execute(['id' => $moto]);
                $motoDetalle = $consulta->fetch();
                if (! $motoDetalle) {
                    throw new \DomainException('Una moto del carrito ya no está disponible. Actualiza el catálogo.');
                }
                if ($color !== null) {
                    $consulta = $this->conexion->prepare('SELECT id,nombre FROM moto_colores WHERE id=:color AND moto_id=:moto');
                    $consulta->execute(['color' => $color,
                        'moto' => $moto]);
                    $filaColor = $consulta->fetch();
                    if (! $filaColor) {
                        throw new \DomainException('El color elegido no corresponde a esa moto. Actualiza el carrito.');
                    }
                    $nombreColor = $filaColor['nombre'];
                } else {
                    $nombreColor = 'Color por confirmar';
                }
                $consulta = $this->conexion->prepare('
                    SELECT precio_oferta FROM ofertas
                    WHERE moto_id = :id AND estado = "Activa"
                      AND fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE()
                    ORDER BY precio_oferta, fecha_fin, id LIMIT 1
                ');
                $consulta->execute(['id' => $moto]);
                $oferta = $consulta->fetch();
                $precio = (float) ($oferta['precio_oferta'] ?? $motoDetalle['precio']);
                $disponible = $this->unidadesParaPedido(
                    $moto,
                    $cantidad,
                    $unidadesAsignadas[$moto] ?? []
                );
                if (count($disponible) < $cantidad) {
                    throw new \DomainException('No hay suficientes unidades disponibles de '.$motoDetalle['marca'].' '.$motoDetalle['modelo'].'.');
                }
                $unidadesAsignadas[$moto] = array_merge($unidadesAsignadas[$moto] ?? [], $disponible);
                $totalLinea = round($precio * $cantidad, 2);
                $total = round($total + $totalLinea, 2);
                $lineas[] = ['moto_id' => $moto,
                    'color_id' => $color,
                    'color' => $nombreColor,
                    'name' => $motoDetalle['marca'].' '.$motoDetalle['modelo'],
                    'image' => $motoDetalle['imagen'],
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $totalLinea,
                    'unidades' => $disponible];
            }
            if ($total <= 0) {
                throw new \DomainException('El carrito no tiene productos válidos.');
            }
            $inicial = (float) ($datos['inicial'] ?? 0);
            $cuotas = (int) ($datos['cuotas'] ?? 0);
            if ($datos['modalidad'] === 'Crédito' && ($inicial < 0 || $inicial >= $total || $cuotas < 0 || $cuotas > 36)) {
                throw new \DomainException('Los datos iniciales de la solicitud de crédito no son válidos.');
            }
            if ($datos['modalidad'] === 'Contado') {
                $inicial = 0;
                $cuotas = 0;
            }
            $estado = $datos['modalidad'] === 'Crédito' ? 'En revisión de crédito' : 'Pendiente de pago';
            if ($datos['modalidad'] === 'Crédito' && empty($datos['acepta_credito'])) {
                throw new \DomainException('Autoriza el uso de tus datos para gestionar la solicitud de crédito.');
            }
            $consulta = $this->conexion->prepare('
                INSERT INTO pedidos
                    (request_id, nombres, apellidos, dni, telefono, correo, modalidad,
                     inicial, numero_cuotas, credito_autorizado_en, subtotal, total, estado, vence_en)
                VALUES
                    (:request, :nombres, :apellidos, :dni, :telefono, :correo, :modalidad,
                     :inicial, :cuotas, IF(:consent = 1, NOW(), NULL), :subtotal, :total, :estado,
                     IF(:expira = "Pendiente de pago", DATE_ADD(NOW(), INTERVAL 20 MINUTE), NULL))
            ');
            $consulta->execute(['request' => $datos['request_id'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'dni' => $datos['dni'],
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'modalidad' => $datos['modalidad'],
                'inicial' => $inicial,
                'cuotas' => $cuotas,
                'consent' => ! empty($datos['acepta_credito']) ? 1 : 0,
                'subtotal' => $total,
                'total' => $total,
                'estado' => $estado,
                'expira' => $estado]);
            $idPedido = (int) $this->conexion->lastInsertId();
            $insercionElemento = $this->conexion->prepare('
                INSERT INTO pedido_items (pedido_id, moto_id, color_id, cantidad, precio_unitario, subtotal)
                VALUES (:pedido, :moto, :color, :cantidad, :precio, :subtotal)
            ');
            $insercionUnidad = $this->conexion->prepare('INSERT INTO pedido_unidades(pedido_item_id,unidad_id) VALUES(:item,:unidad)');
            $reserva = $this->conexion->prepare('UPDATE unidades_moto SET estado="Reservada" WHERE id=:unidad AND estado="Disponible"');
            foreach ($lineas as $linea) {
                $insercionElemento->execute(['pedido' => $idPedido,
                    'moto' => $linea['moto_id'],
                    'color' => $linea['color_id'],
                    'cantidad' => $linea['cantidad'],
                    'precio' => $linea['precio'],
                    'subtotal' => $linea['subtotal']]);
                $idElemento = (int) $this->conexion->lastInsertId();
                if ($estado === 'Pendiente de pago') {
                    foreach ($linea['unidades'] as $idUnidad) {
                        $reserva->execute(['unidad' => $idUnidad]);
                        if ($reserva->rowCount() !== 1) {
                            throw new \DomainException('Cambió el stock mientras se procesaba el pedido. Vuelve a intentarlo.');
                        }
                        $insercionUnidad->execute(['item' => $idElemento,
                            'unidad' => $idUnidad]);
                    }
                }
            }
            $this->conexion->commit();

            return ['id' => $idPedido,
                'modalidad' => $datos['modalidad'],
                'estado' => $estado,
                'total' => $total,
                'checkout_url' => null,
                'mp_order_id' => null,
                'items' => $lineas,
                'repetido' => false];
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    private function verificarReintento(array $pedido, array $elementos, array $datos): void
    {
        foreach (['nombres', 'apellidos', 'dni', 'telefono', 'correo', 'modalidad'] as $campo) {
            if ((string) $pedido[$campo] !== (string) $datos[$campo]) {
                throw new \DomainException('El intento de compra cambió. Actualiza el carrito para crear un nuevo pedido.');
            }
        }

        if ($datos['modalidad'] === 'Crédito'
            && ((float) $pedido['inicial'] !== (float) $datos['inicial']
                || (int) $pedido['numero_cuotas'] !== (int) $datos['cuotas'])) {
            throw new \DomainException('Los datos del crédito cambiaron. Actualiza el carrito para crear un nuevo pedido.');
        }

        $lineas = static function (array $productos): array {
            $resultado = [];
            foreach ($productos as $producto) {
                $clave = (int) $producto['moto_id'].':'.(int) ($producto['color_id'] ?? 0);
                $resultado[$clave] = (int) $producto['cantidad'];
            }
            ksort($resultado);

            return $resultado;
        };

        if ($lineas($elementos) !== $lineas($datos['items'])) {
            throw new \DomainException('Los productos cambiaron. Actualiza el carrito para crear un nuevo pedido.');
        }
    }

    private function unidadesParaPedido(int $motoId, int $cantidad, array $excluidas): array
    {
        if ($cantidad < 1 || $cantidad > 20) {
            throw new \DomainException('La cantidad de unidades solicitadas no es válida.');
        }

        $consultaSql = 'SELECT id FROM unidades_moto WHERE moto_id = ? AND estado = "Disponible"';
        $parametros = [$motoId];

        if ($excluidas !== []) {
            $marcadores = implode(', ', array_fill(0, count($excluidas), '?'));
            $consultaSql .= ' AND id NOT IN ('.$marcadores.')';
            $parametros = array_merge($parametros, $excluidas);
        }

        $consultaSql .= ' ORDER BY id LIMIT '.$cantidad.' FOR UPDATE';
        $consulta = $this->conexion->prepare($consultaSql);
        $consulta->execute($parametros);

        return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    public function guardarMercadoPago(int $id, string $idMercadoPago, string $url): void
    {
        $consulta = $this->conexion->prepare('
            UPDATE pedidos
            SET mp_order_id = :mp, checkout_url = :url, estado = "Pendiente de pago",
                vence_en = DATE_ADD(NOW(), INTERVAL 20 MINUTE)
            WHERE id = :id AND modalidad = "Contado"
              AND estado IN ("Pendiente de pago", "Error de pago")
        ');
        $consulta->execute(['mp' => $idMercadoPago,
            'url' => $url,
            'id' => $id]);
        if ($consulta->rowCount() !== 1) {
            throw new \DomainException('El pedido cambió de estado y no se pudo vincular el pago.');
        }
    }

    public function errorPago(int $id): void
    {
        $this->conexion->beginTransaction();
        try {
            $this->liberarUnidades($id);
            $consulta = $this->conexion->prepare('UPDATE pedidos SET estado="Error de pago",checkout_url=NULL WHERE id=:id AND estado="Pendiente de pago"');
            $consulta->execute(['id' => $id]);
            $this->conexion->commit();
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPorRequest(string $idSolicitud): ?array
    {
        $consulta = $this->conexion->prepare('SELECT id,request_id,modalidad,total,estado,mp_order_id,checkout_url FROM pedidos WHERE request_id=:id');
        $consulta->execute(['id' => $idSolicitud]);

        return $consulta->fetch() ?: null;
    }

    public function obtenerPorMercadoPago(string $idMercadoPago): ?array
    {
        $consulta = $this->conexion->prepare('SELECT id,request_id,modalidad,total,estado,mp_order_id,checkout_url FROM pedidos WHERE mp_order_id=:id');
        $consulta->execute(['id' => $idMercadoPago]);

        return $consulta->fetch() ?: null;
    }

    public function solicitudesCredito(): array
    {
        return $this->conexion->query('
            SELECT p.id, p.nombres, p.apellidos, p.dni, p.telefono, p.correo,
                   p.inicial, p.numero_cuotas, p.total, p.estado, p.creado_en,
                   p.credito_autorizado_en, items.motocicletas, items.unidades_solicitadas,
                   (SELECT COUNT(*) FROM solicitud_credito_ventas sv
                    JOIN pedido_items linea ON linea.id = sv.pedido_item_id
                    JOIN ventas venta ON venta.id = sv.venta_id
                    WHERE linea.pedido_id = p.id AND venta.estado = "Confirmada") AS ventas_registradas
            FROM pedidos p
            JOIN (
                SELECT pi.pedido_id,
                       GROUP_CONCAT(CONCAT(ma.nombre, " ", m.modelo, " ×", pi.cantidad)
                                    ORDER BY pi.id SEPARATOR ", ") motocicletas,
                       SUM(pi.cantidad) unidades_solicitadas
                FROM pedido_items pi
                JOIN motos m ON m.id = pi.moto_id
                JOIN marcas ma ON ma.id = m.marca_id
                GROUP BY pi.pedido_id
            ) items ON items.pedido_id = p.id
            WHERE p.modalidad = "Crédito"
              AND p.estado IN ("En revisión de crédito", "Crédito aprobado", "Crédito no aprobado")
            ORDER BY p.creado_en ASC
        ')->fetchAll();
    }

    public function contarSolicitudesCreditoPendientes(): int
    {
        return (int) $this->conexion->query('SELECT COUNT(*) FROM pedidos WHERE modalidad="Crédito" AND estado="En revisión de crédito"')->fetchColumn();
    }

    public function evaluarSolicitudCredito(int $id, string $decision): void
    {
        $estado = match ($decision) {
            'aprobar' => 'Crédito aprobado',
            'rechazar' => 'Crédito no aprobado',
            default => throw new \InvalidArgumentException('Selecciona una decisión válida.'),
        };
        $this->conexion->beginTransaction();
        try {
            $consulta = $this->conexion->prepare('
                SELECT nombres, apellidos, dni, telefono, correo, estado
                FROM pedidos
                WHERE id = :id AND modalidad = "Crédito"
                FOR UPDATE
            ');
            $consulta->execute(['id' => $id]);
            $solicitud = $consulta->fetch();
            if (! $solicitud || $solicitud['estado'] !== 'En revisión de crédito') {
                throw new \DomainException('La solicitud ya fue evaluada o no está disponible.');
            }
            if ($decision === 'aprobar') {
                $consulta = $this->conexion->prepare('
                    SELECT id, estado
                    FROM clientes
                    WHERE dni = :dni
                ');
                $consulta->execute(['dni' => $solicitud['dni']]);
                $cliente = $consulta->fetch();
                if (! $cliente) {
                    $consulta = $this->conexion->prepare('
                        INSERT INTO clientes (dni, nombres, apellidos, telefono, correo)
                        VALUES (:dni, :nombres, :apellidos, :telefono, :correo)
                    ');
                    $consulta->execute([
                        'dni' => $solicitud['dni'],
                        'nombres' => $solicitud['nombres'],
                        'apellidos' => $solicitud['apellidos'],
                        'telefono' => $solicitud['telefono'],
                        'correo' => $solicitud['correo'],
                    ]);
                } elseif ($cliente['estado'] !== 'Activo') {
                    $consulta = $this->conexion->prepare('
                        UPDATE clientes
                        SET estado = "Activo"
                        WHERE id = :id
                    ');
                    $consulta->execute(['id' => $cliente['id']]);
                }
            }
            $consulta = $this->conexion->prepare('
                UPDATE pedidos
                SET estado = :estado
                WHERE id = :id AND estado = "En revisión de crédito"
            ');
            $consulta->execute([
                'estado' => $estado,
                'id' => $id,
            ]);
            if ($consulta->rowCount() !== 1) {
                throw new \DomainException('La solicitud ya fue evaluada o no está disponible.');
            }
            $this->conexion->commit();
        } catch (\Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    public function clienteAprobadoPorPedido(int $id): ?int
    {
        $consulta = $this->conexion->prepare('
            SELECT c.id FROM pedidos p
            JOIN clientes c ON c.dni = p.dni AND c.estado = "Activo"
            WHERE p.id = :id AND p.modalidad = "Crédito" AND p.estado = "Crédito aprobado"
        ');
        $consulta->execute(['id' => $id]);
        $id = $consulta->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    public function actualizarEstadoPago(int $id, string $idMercadoPago, string $estado): void
    {
        $this->conexion->beginTransaction();
        try {
            $consulta = $this->conexion->prepare('SELECT * FROM pedidos WHERE id=:id FOR UPDATE');
            $consulta->execute(['id' => $id]);
            $pedido = $consulta->fetch();
            if (! $pedido || $pedido['mp_order_id'] !== $idMercadoPago) {
                throw new \DomainException('No se encontró el pedido de Mercado Pago vinculado.');
            }
            if ($estado === 'processed') {
                if ($pedido['estado'] === 'Pagado') {
                    $this->conexion->commit();

                    return;
                }
                $consulta = $this->conexion->prepare('
                    SELECT pi.id item_id, pi.cantidad, pi.precio_unitario, pi.subtotal, pu.unidad_id
                    FROM pedido_items pi
                    LEFT JOIN pedido_unidades pu ON pu.pedido_item_id = pi.id
                    WHERE pi.pedido_id = :id ORDER BY pi.id FOR UPDATE
                ');
                $consulta->execute(['id' => $id]);
                $filas = $consulta->fetchAll();
                $elementos = [];
                foreach ($filas as $fila) {
                    $idElemento = (int) $fila['item_id'];
                    if (! isset($elementos[$idElemento])) {
                        $elementos[$idElemento] = ['cantidad' => (int) $fila['cantidad'],
                            'precio' => (float) $fila['precio_unitario'],
                            'subtotal' => (float) $fila['subtotal'],
                            'unidades' => []];
                    }
                    if ($fila['unidad_id'] !== null) {
                        $elementos[$idElemento]['unidades'][] = (int) $fila['unidad_id'];
                    }
                }
                foreach ($elementos as $elemento) {
                    if (count($elemento['unidades']) !== $elemento['cantidad']) {
                        throw new \DomainException('El pedido no conserva todas las unidades reservadas; se requiere revisión manual.');
                    }
                }
                $consulta = $this->conexion->prepare('SELECT id FROM clientes WHERE dni=:dni');
                $consulta->execute(['dni' => $pedido['dni']]);
                $idCliente = (int) ($consulta->fetchColumn() ?: 0);
                if (! $idCliente) {
                    $consulta = $this->conexion->prepare('
                        INSERT INTO clientes (dni, nombres, apellidos, telefono, correo, estado)
                        VALUES (:dni, :nombres, :apellidos, :telefono, :correo, "Activo")
                    ');
                    $consulta->execute(['dni' => $pedido['dni'],
                        'nombres' => $pedido['nombres'],
                        'apellidos' => $pedido['apellidos'],
                        'telefono' => $pedido['telefono'],
                        'correo' => $pedido['correo']]);
                    $idCliente = (int) $this->conexion->lastInsertId();
                }
                $consulta = $this->conexion->query('
                    SELECT u.id FROM usuarios u
                    JOIN roles r ON r.id = u.rol_id
                    WHERE r.nombre = "Administrador" AND u.estado = "Activo"
                    ORDER BY u.id LIMIT 1
                ');
                $idVendedor = (int) $consulta->fetchColumn();
                if (! $idVendedor) {
                    throw new \DomainException('No existe un usuario administrador activo para registrar la venta pagada.');
                }
                $venta = $this->conexion->prepare('
                    INSERT INTO ventas (cliente_id, usuario_id, unidad_id, precio, descuento, total, modalidad, estado)
                    VALUES (:cliente, :usuario, :unidad, :precio, 0, :total, "Contado", "Confirmada")
                ');
                foreach ($elementos as $elemento) {
                    foreach ($elemento['unidades'] as $idUnidad) {
                        $venta->execute(['cliente' => $idCliente,
                            'usuario' => $idVendedor,
                            'unidad' => $idUnidad,
                            'precio' => $elemento['precio'],
                            'total' => round($elemento['precio'],
                                2)]);
                    }
                }
                $consulta = $this->conexion->prepare('
                    UPDATE unidades_moto u
                    JOIN pedido_unidades pu ON pu.unidad_id = u.id
                    JOIN pedido_items pi ON pi.id = pu.pedido_item_id
                    SET u.estado = "Vendida"
                    WHERE pi.pedido_id = :id AND u.estado = "Reservada"
                ');
                $consulta->execute(['id' => $id]);
                $consulta = $this->conexion->prepare('UPDATE pedidos SET estado="Pagado" WHERE id=:id');
                $consulta->execute(['id' => $id]);
            } elseif (in_array($estado, ['canceled',
                'expired'], true)) {
                $this->liberarUnidades($id);
                $consulta = $this->conexion->prepare('UPDATE pedidos SET estado="Cancelado" WHERE id=:id');
                $consulta->execute(['id' => $id]);
            }
            $this->conexion->commit();
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    public function liberarPedidosVencidos(): void
    {
        $vencido = $this->conexion->query('SELECT id FROM pedidos WHERE estado="Pendiente de pago" AND vence_en<NOW()')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($vencido as $id) {
            $this->liberarUnidades((int) $id);
        }
        $this->conexion->exec('UPDATE pedidos SET estado="Cancelado" WHERE estado="Pendiente de pago" AND vence_en<NOW()');
    }

    private function liberarUnidades(int $id): void
    {
        $consulta = $this->conexion->prepare('
            UPDATE unidades_moto u
            JOIN pedido_unidades pu ON pu.unidad_id = u.id
            JOIN pedido_items pi ON pi.id = pu.pedido_item_id
            SET u.estado = "Disponible"
            WHERE pi.pedido_id = :id AND u.estado = "Reservada"
        ');
        $consulta->execute(['id' => $id]);
        $consulta = $this->conexion->prepare('DELETE pu FROM pedido_unidades pu JOIN pedido_items pi ON pi.id=pu.pedido_item_id WHERE pi.pedido_id=:id');
        $consulta->execute(['id' => $id]);
    }

    private function items(int $id): array
    {
        $consulta = $this->conexion->prepare('
            SELECT pi.moto_id, pi.color_id, pi.cantidad, pi.precio_unitario precio,
                   pi.subtotal, ma.nombre marca, m.modelo,
                   CONCAT(ma.nombre, " ", m.modelo) name,
                   COALESCE(mc.nombre, "Color por confirmar") color
            FROM pedido_items pi
            JOIN motos m ON m.id = pi.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            LEFT JOIN moto_colores mc ON mc.id = pi.color_id
            WHERE pi.pedido_id = :id
        ');
        $consulta->execute(['id' => $id]);

        return $consulta->fetchAll();
    }
}
