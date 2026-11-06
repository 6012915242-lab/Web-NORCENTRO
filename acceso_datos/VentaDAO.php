<?php

declare(strict_types=1);

namespace App\AccesoDatos;

use App\Modelos\Cliente;
use App\Modelos\Credito;
use App\Modelos\Moto;
use App\Modelos\PlanCredito;
use App\Modelos\Venta;
use App\Nucleo\ModeloBaseORM;
use DateTimeImmutable;
use PDO;

/** Registra ventas, créditos, cuotas y pagos dentro de transacciones. */
final class VentaDAO extends ModeloBaseORM
{
    public function obtenerConRelaciones(int $id): ?Venta
    {
        $registro = $this->obtenerUno(
            'SELECT v.*, c.id AS cliente_relacion_id, c.dni AS cliente_dni,
                    c.nombres AS cliente_nombres, c.apellidos AS cliente_apellidos,
                    c.telefono AS cliente_telefono, c.direccion AS cliente_direccion,
                    c.correo AS cliente_correo, c.estado AS cliente_estado,
                    m.id AS moto_relacion_id, m.marca_id, ma.nombre AS marca,
                    m.modelo, m.cilindrada, m.color, m.precio AS moto_precio,
                    m.descripcion, m.anio, m.imagen, m.estado AS moto_estado
             FROM ventas v
             JOIN clientes c ON c.id = v.cliente_id
             JOIN unidades_moto u ON u.id = v.unidad_id
             JOIN motos m ON m.id = u.moto_id
             JOIN marcas ma ON ma.id = m.marca_id
             WHERE v.id = :id',
            ['id' => $id]
        );

        if ($registro === null) {
            return null;
        }

        $venta = Venta::desdeRegistro($registro);
        $venta->cliente = Cliente::desdeRegistro([
            'id' => $registro['cliente_relacion_id'],
            'dni' => $registro['cliente_dni'],
            'nombres' => $registro['cliente_nombres'],
            'apellidos' => $registro['cliente_apellidos'],
            'telefono' => $registro['cliente_telefono'],
            'direccion' => $registro['cliente_direccion'],
            'correo' => $registro['cliente_correo'],
            'estado' => $registro['cliente_estado'],
        ]);
        $venta->moto = Moto::desdeRegistro([
            'id' => $registro['moto_relacion_id'],
            'marca_id' => $registro['marca_id'],
            'marca' => $registro['marca'],
            'modelo' => $registro['modelo'],
            'cilindrada' => $registro['cilindrada'],
            'color' => $registro['color'],
            'precio' => $registro['moto_precio'],
            'descripcion' => $registro['descripcion'],
            'anio' => $registro['anio'],
            'imagen' => $registro['imagen'],
            'estado' => $registro['moto_estado'],
        ]);

        return $venta;
    }

    public function obtenerCredito(int $id): ?Credito
    {
        return $this->hidratarEntidad(
            Credito::class,
            'SELECT * FROM creditos WHERE id = :id',
            ['id' => $id]
        );
    }

    public function listar(): array
    {
        return $this->conexion->query('
            SELECT v.*, c.nombres, c.apellidos, u.codigo_serie, m.modelo,
                   ma.nombre marca, us.nombre vendedor,
                   COALESCE((SELECT SUM(cr.importe_recargo) FROM creditos cr
                             WHERE cr.venta_id = v.id), 0) AS recargo_credito
            FROM ventas v
            JOIN clientes c ON c.id = v.cliente_id
            JOIN unidades_moto u ON u.id = v.unidad_id
            JOIN motos m ON m.id = u.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            JOIN usuarios us ON us.id = v.usuario_id
            ORDER BY v.fecha DESC
        ')->fetchAll();
    }

    /**
     * Bloquea el cliente y la unidad durante la venta; si falla, revierte toda la operación.
     */
    public function crear(array $datos): int
    {
        $this->conexion->beginTransaction();
        try {
            $cliente = $this->conexion->prepare('SELECT id FROM clientes WHERE id=:id AND estado="Activo" FOR UPDATE');
            $cliente->bindValue(':id', (int) $datos['cliente_id'], PDO::PARAM_INT);
            $cliente->execute();
            if (! $cliente->fetchColumn()) {
                throw new \DomainException('Selecciona un cliente activo para la venta.');
            }
            $consulta = $this->conexion->prepare('
                SELECT u.id, u.estado, u.moto_id, m.precio
                FROM unidades_moto u
                JOIN motos m ON m.id = u.moto_id
                JOIN marcas ma ON ma.id = m.marca_id
                WHERE u.id = :id AND m.estado = "Activo" AND ma.estado = "Activo"
                FOR UPDATE
            ');
            $consulta->bindValue(':id', (int) $datos['unidad_id'], PDO::PARAM_INT);
            $consulta->execute();
            $unidad = $consulta->fetch();
            if (! $unidad || $unidad['estado'] !== 'Disponible') {
                throw new \DomainException('La unidad seleccionada ya no está disponible. Actualiza la lista.');
            }
            $itemSolicitud = null;
            if (($datos['pedido_id'] ?? null) !== null) {
                $itemSolicitud = $this->buscarItemSolicitud(
                    (int) $datos['pedido_id'],
                    (int) $datos['cliente_id'],
                    (int) $unidad['moto_id']
                );
            }
            if ((float) $datos['descuento'] < 0 || (float) $datos['descuento'] >= (float) $unidad['precio']) {
                throw new \DomainException('El descuento debe ser menor que el precio de la motocicleta.');
            }
            $precio = (float) $unidad['precio'];
            $total = $precio - (float) $datos['descuento'];
            $consulta = $this->conexion->prepare('
                INSERT INTO ventas (cliente_id, usuario_id, unidad_id, precio, descuento, total, modalidad)
                VALUES (:cliente, :usuario, :unidad, :precio, :descuento, :total, :modalidad)
            ');
            $consulta->execute(['cliente' => $datos['cliente_id'],
                'usuario' => $datos['usuario_id'],
                'unidad' => $datos['unidad_id'],
                'precio' => $precio,
                'descuento' => $datos['descuento'],
                'total' => $total,
                'modalidad' => $datos['modalidad']]);
            $venta = (int) $this->conexion->lastInsertId();
            if ($itemSolicitud !== null) {
                $this->ejecutar(
                    'INSERT INTO solicitud_credito_ventas (pedido_item_id, venta_id) VALUES (:item, :venta)',
                    ['item' => $itemSolicitud, 'venta' => $venta]
                );
            }
            $consulta = $this->conexion->prepare('UPDATE unidades_moto SET estado="Vendida" WHERE id=:id AND estado="Disponible"');
            $consulta->execute(['id' => $datos['unidad_id']]);
            if ($consulta->rowCount() !== 1) {
                throw new \DomainException('No se pudo reservar la unidad para esta venta.');
            }
            if ($datos['modalidad'] === 'Crédito') {
                $this->crearCredito($venta, $datos, $total);
            }
            $this->conexion->commit();

            return $venta;
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }

    private function buscarItemSolicitud(int $pedidoId, int $clienteId, int $motoId): int
    {
        $solicitud = $this->obtenerUno(
            'SELECT p.id
             FROM pedidos p
             INNER JOIN clientes c ON c.dni = p.dni
             WHERE p.id = :pedido
               AND c.id = :cliente
               AND p.modalidad = "Crédito"
               AND p.estado = "Crédito aprobado"
             FOR UPDATE',
            ['pedido' => $pedidoId, 'cliente' => $clienteId]
        );
        if ($solicitud === null) {
            throw new \DomainException('La solicitud no está aprobada o no corresponde al cliente.');
        }

        $elementosPedido = $this->obtenerTodos(
            'SELECT id, cantidad
             FROM pedido_items
             WHERE pedido_id = :pedido AND moto_id = :moto
             ORDER BY id
             FOR UPDATE',
            ['pedido' => $pedidoId, 'moto' => $motoId]
        );
        foreach ($elementosPedido as $elementoPedido) {
            $cantidadVinculada = (int) $this->preparar(
                'SELECT COUNT(*)
                 FROM solicitud_credito_ventas vinculo
                 INNER JOIN ventas venta ON venta.id = vinculo.venta_id
                 WHERE vinculo.pedido_item_id = :item
                   AND venta.estado = "Confirmada"',
                ['item' => $elementoPedido['id']]
            )->fetchColumn();
            if ($cantidadVinculada < (int) $elementoPedido['cantidad']) {
                return (int) $elementoPedido['id'];
            }
        }

        throw new \DomainException('Esta moto no está pendiente en la solicitud aprobada.');
    }

    private function crearCredito(int $venta, array $datos, float $total): void
    {
        $inicial = (float) $datos['inicial'];
        $numeroCuotas = (int) $datos['numero_cuotas'];
        $plan = PlanCredito::calcular($total, $inicial, $numeroCuotas);
        $financiado = $plan['financiado'];
        $consulta = $this->conexion->prepare('
            INSERT INTO creditos
                (venta_id, inicial, monto_financiado, numero_cuotas, saldo,
                 estado, porcentaje_recargo, importe_recargo)
            VALUES (:venta, :inicial, :financiado, :n, :saldo, "Activo", :porcentaje, :recargo)
        ');
        $consulta->execute(['venta' => $venta,
            'inicial' => $inicial,
            'financiado' => $financiado,
            'n' => $numeroCuotas,
            'saldo' => $financiado,
            'porcentaje' => PlanCredito::PORCENTAJE_RECARGO,
            'recargo' => $plan['recargo']]);
        $credito = (int) $this->conexion->lastInsertId();
        $primeraFecha = new DateTimeImmutable($datos['fecha_primera_cuota']);
        $fechas = PlanCredito::fechasCuotas($primeraFecha, $numeroCuotas);
        $instancia = $this->conexion->prepare('INSERT INTO cuotas(credito_id,numero,fecha_vencimiento,monto,estado) VALUES(:credito,:numero,:fecha,:monto,"Pendiente")');
        for ($i = 1; $i <= $numeroCuotas; $i++) {
            $monto = $plan['cuotas'][$i - 1];
            $instancia->execute(['credito' => $credito,
                'numero' => $i,
                'fecha' => $fechas[$i - 1]->format('Y-m-d'),
                'monto' => $monto]);
        }
        if ($inicial > 0) {
            $p = $this->conexion->prepare('INSERT INTO pagos(credito_id,cuota_id,monto,fecha,concepto) VALUES(:credito,NULL,:monto,CURDATE(),"Cuota inicial")');
            $p->execute(['credito' => $credito,
                'monto' => $inicial]);
        }
    }

    public function creditos(): array
    {
        return $this->conexion->query('
            SELECT cr.*, v.id venta_id, c.nombres, c.apellidos, m.modelo, ma.nombre marca
            FROM creditos cr
            JOIN ventas v ON v.id = cr.venta_id
            JOIN clientes c ON c.id = v.cliente_id
            JOIN unidades_moto u ON u.id = v.unidad_id
            JOIN motos m ON m.id = u.moto_id
            JOIN marcas ma ON ma.id = m.marca_id
            ORDER BY cr.id DESC
        ')->fetchAll();
    }

    public function detallesCreditos(array $identificadores): array
    {
        $identificadores = array_values(array_unique(array_filter(
            array_map('intval', $identificadores),
            static fn (int $id): bool => $id > 0
        )));

        $resultado = ['cuotas' => [], 'pagos' => []];
        if ($identificadores === []) {
            return $resultado;
        }

        foreach ($identificadores as $id) {
            $resultado['cuotas'][$id] = [];
            $resultado['pagos'][$id] = [];
        }

        $marcadores = implode(', ', array_fill(0, count($identificadores), '?'));
        $actualizar = $this->conexion->prepare("
            UPDATE cuotas SET estado = 'Vencida'
            WHERE credito_id IN ($marcadores)
              AND estado = 'Pendiente' AND fecha_vencimiento < CURDATE()
        ");
        $actualizar->execute($identificadores);

        $cuotas = $this->conexion->prepare("
            SELECT * FROM cuotas WHERE credito_id IN ($marcadores)
            ORDER BY credito_id, numero
        ");
        $cuotas->execute($identificadores);
        foreach ($cuotas->fetchAll() as $cuota) {
            $resultado['cuotas'][(int) $cuota['credito_id']][] = $cuota;
        }

        $pagos = $this->conexion->prepare("
            SELECT p.credito_id, p.id, p.monto, p.fecha, p.concepto,
                   q.numero AS cuota_numero
            FROM pagos p
            LEFT JOIN cuotas q ON q.id = p.cuota_id
            WHERE p.credito_id IN ($marcadores)
            ORDER BY p.credito_id, p.fecha DESC, p.id DESC
        ");
        $pagos->execute($identificadores);
        foreach ($pagos->fetchAll() as $pago) {
            $resultado['pagos'][(int) $pago['credito_id']][] = $pago;
        }

        return $resultado;
    }

    public function pagarCuota(int $cuotaId, float $monto): void
    {
        $this->conexion->beginTransaction();
        try {
            $consulta = $this->conexion->prepare('SELECT q.*,c.saldo,c.id credito_id FROM cuotas q JOIN creditos c ON c.id=q.credito_id WHERE q.id=:id FOR UPDATE');
            $consulta->execute(['id' => $cuotaId]);
            $resultado = $consulta->fetch();
            if (! $resultado || $resultado['estado'] === 'Pagada') {
                throw new \DomainException('La cuota ya fue pagada o no existe.');
            }
            $pendiente = round((float) $resultado['monto'] - (float) $resultado['pagado'], 2);
            if ($monto <= 0 || $monto > $pendiente || $monto > (float) $resultado['saldo']) {
                throw new \DomainException('El pago debe ser mayor que cero y no superar el saldo de la cuota.');
            }
            $pagado = round((float) $resultado['pagado'] + $monto, 2);
            $estado = $pagado >= (float) $resultado['monto'] ? 'Pagada' : ((string) $resultado['fecha_vencimiento'] < date('Y-m-d') ? 'Vencida' : 'Pendiente');
            $consulta = $this->conexion->prepare('UPDATE cuotas SET pagado=:pagado,estado=:estado WHERE id=:id');
            $consulta->execute(['pagado' => $pagado,
                'estado' => $estado,
                'id' => $cuotaId]);
            $consulta = $this->conexion->prepare('UPDATE creditos SET estado=IF(saldo<=:monto_estado,"Cancelado","Activo"),saldo=GREATEST(0,saldo-:monto_restar) WHERE id=:id');
            $consulta->execute(['monto_estado' => $monto,
                'monto_restar' => $monto,
                'id' => $resultado['credito_id']]);
            $consulta = $this->conexion->prepare('INSERT INTO pagos(credito_id,cuota_id,monto,fecha,concepto) VALUES(:credito,:cuota,:monto,CURDATE(),"Abono de cuota")');
            $consulta->execute(['credito' => $resultado['credito_id'],
                'cuota' => $cuotaId,
                'monto' => $monto]);
            $this->conexion->commit();
        } catch (\Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $e;
        }
    }
}
