<?php
use App\Configuracion\Herramientas;

$creditosPendientes = count(array_filter(
    $solicitudesCredito,
    static fn ($solicitud) => $solicitud['estado'] === 'En revisión de crédito'
));
$cantidadCreditosActivos = 0;
$cantidadCuotasVencidas = 0;
$saldoCreditoActivo = 0.0;

foreach ($creditos as $credito) {
    if ($credito['estado'] === 'Activo' && (float) $credito['saldo'] > 0) {
        $cantidadCreditosActivos++;
        $saldoCreditoActivo += (float) $credito['saldo'];
    }

    foreach ($cuotas[$credito['id']] ?? [] as $cuota) {
        if ($cuota['estado'] === 'Vencida') {
            $cantidadCuotasVencidas++;
        }
    }
}
?>

<div class="page-head">
    <div>
        <span class="eyebrow">Financiamiento</span>
        <h1>Créditos y cuotas</h1>
        <p>Revisa las solicitudes recibidas y el seguimiento de los créditos activos.</p>
    </div>
</div>

<?php if ($idVentaResaltada > 0): ?>
    <div class="notice info sale-just-created">
        Se registró la venta #<?= $idVentaResaltada ?> a crédito. El cronograma está listo;
        abre el seguimiento para registrar pagos y ver cómo cambia el saldo.
        <a class="text-link" href="#credit-sale-<?= $idVentaResaltada ?>">Ir a sus cuotas ↓</a>
    </div>
<?php endif; ?>

<section class="stats-grid credit-overview" aria-label="Resumen de cartera">
    <div class="stat-card">
        <span>Solicitudes por revisar</span>
        <strong><?= $creditosPendientes ?></strong>
        <small>Requieren decisión del equipo</small>
    </div>
    <div class="stat-card">
        <span>Créditos activos</span>
        <strong><?= $cantidadCreditosActivos ?></strong>
        <small>Con saldo pendiente</small>
    </div>
    <div class="stat-card <?= $cantidadCuotasVencidas ? 'credit-stat-alert' : '' ?>">
        <span>Cuotas vencidas</span>
        <strong><?= $cantidadCuotasVencidas ?></strong>
        <small><?= $cantidadCuotasVencidas ? 'Revisar y contactar al cliente' : 'No hay cuotas vencidas' ?></small>
    </div>
    <div class="stat-card accent">
        <span>Saldo total pendiente</span>
        <strong class="credit-balance-total"><?= Herramientas::formatearDinero($saldoCreditoActivo) ?></strong>
        <small>En créditos activos</small>
    </div>
</section>

<?php if ($usuarioPuede('creditos.revisar')): ?>
    <section class="panel credit-requests-panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Gestión de administrador y vendedor</span>
                <h2>Solicitudes de compra a crédito</h2>
            </div>
            <span class="pill amber"><?= $creditosPendientes ?> por revisar</span>
        </div>

        <?php if ($solicitudesCredito): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Solicitud</th>
                            <th>Cliente y DNI</th>
                            <th>Motocicleta</th>
                            <th>Inicial y cuotas propuestas</th>
                            <th>Fecha y autorización</th>
                            <th>Contacto</th>
                            <th>Evaluación interna</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudesCredito as $solicitud): ?>
                            <?php
                            $telefono = preg_replace('/\D/', '', (string) $solicitud['telefono']);
                            $telefonoWhatsapp = str_starts_with($telefono, '51') ? $telefono : '51' . $telefono;
                            $inicialPropuesta = (float) $solicitud['inicial'] > 0
                                ? Herramientas::formatearDinero($solicitud['inicial'])
                                : 'por coordinar';
                            $plazoPropuesto = (int) $solicitud['numero_cuotas'] > 0
                                ? (int) $solicitud['numero_cuotas'] . ' cuotas'
                                : 'por coordinar';
                            $propuesta = 'Inicial ' . $inicialPropuesta . ' · plazo ' . $plazoPropuesto;
                            $mensaje = 'Hola ' . $solicitud['nombres'] . ', recibimos tu solicitud de crédito para '
                                . $solicitud['motocicletas'] . '. ' . $propuesta
                                . '. El equipo Norcentro quiere continuar con la evaluación.';
                            $claseEstado = 'amber';
                            if ($solicitud['estado'] === 'Crédito aprobado') {
                                $claseEstado = 'green';
                            } elseif ($solicitud['estado'] === 'Crédito no aprobado') {
                                $claseEstado = 'red';
                            }
                            $estadoPendiente = $solicitud['estado'] === 'En revisión de crédito';
                            ?>
                            <tr class="<?= $estadoPendiente ? 'credit-request-pending' : '' ?>">
                                <td>
                                    <b>#<?= (int) $solicitud['id'] ?></b>
                                    <small><?= Herramientas::formatearDinero($solicitud['total']) ?></small>
                                    <span class="pill <?= $claseEstado ?>">
                                        <?= Herramientas::escaparHtml($estadoPendiente ? 'Pendiente del asesor' : $solicitud['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= Herramientas::escaparHtml($solicitud['nombres'] . ' ' . $solicitud['apellidos']) ?>
                                    <small>DNI <?= Herramientas::escaparHtml($solicitud['dni']) ?></small>
                                </td>
                                <td><?= Herramientas::escaparHtml($solicitud['motocicletas']) ?></td>
                                <td>
                                    <?= Herramientas::escaparHtml($propuesta) ?>
                                    <small>Condiciones sujetas a evaluación</small>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($solicitud['creado_en'])) ?>
                                    <small><?= $solicitud['credito_autorizado_en'] ? 'Autorizó uso de datos' : 'Sin autorización registrada' ?></small>
                                </td>
                                <td>
                                    <a class="text-link"
                                       href="https://wa.me/<?= Herramientas::escaparHtml($telefonoWhatsapp) ?>?text=<?= rawurlencode($mensaje) ?>"
                                       target="_blank" rel="noopener">Contactar ↗</a>
                                </td>
                                <td>
                                    <?php if ($estadoPendiente): ?>
                                        <form method="post" class="credit-decision-form">
                                            <?= Herramientas::campoCsrf() ?>
                                            <input type="hidden" name="action" value="evaluar_solicitud_credito">
                                            <input type="hidden" name="pedido_id" value="<?= (int) $solicitud['id'] ?>">
                                            <button class="button small-button" name="decision" value="aprobar">Aprobar</button>
                                            <button class="button small-button subtle" name="decision" value="rechazar">No aprobar</button>
                                        </form>
                                    <?php elseif ($solicitud['estado'] === 'Crédito aprobado'): ?>
                                        <a class="text-link" href="index.php?page=ventas&amp;pedido_id=<?= (int) $solicitud['id'] ?>">
                                            Registrar venta →
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="muted small credit-review-note">
                El administrador o vendedor revisa la identidad y los documentos con los medios autorizados
                por Norcentro, registra la decisión aquí y luego confirma la operación en Ventas para definir
                la inicial y generar las cuotas. La solicitud no se aprueba automáticamente.
            </p>
        <?php else: ?>
            <div class="empty-state">
                <h3>No hay solicitudes de crédito</h3>
                <p>Las nuevas solicitudes aparecerán aquí para su revisión.</p>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php foreach ($creditos as $credito): ?>
    <?php
    $montoFinanciado = (float) $credito['monto_financiado'];
    $montoFinanciadoPagado = max(0, $montoFinanciado - (float) $credito['saldo']);
    $porcentajePagado = $montoFinanciado > 0
        ? min(100, (int) round($montoFinanciadoPagado / $montoFinanciado * 100))
        : 100;
    ?>
    <section class="panel credit-panel <?= (int) $credito['venta_id'] === $idVentaResaltada ? 'sale-just-created' : '' ?>"
             id="credit-sale-<?= (int) $credito['venta_id'] ?>">
        <div class="credit-summary">
            <div>
                <span class="eyebrow">
                    Crédito #<?= (int) $credito['id'] ?> · venta #<?= (int) $credito['venta_id'] ?>
                </span>
                <h2><?= Herramientas::escaparHtml($credito['marca'] . ' ' . $credito['modelo']) ?></h2>
                <p>
                    <?= Herramientas::escaparHtml($credito['nombres'] . ' ' . $credito['apellidos']) ?> ·
                    Inicial <?= Herramientas::formatearDinero($credito['inicial']) ?> ·
                    <?= (int) $credito['numero_cuotas'] ?> cuotas
                </p>
                <div class="credit-progress" role="progressbar" aria-label="Porcentaje del monto financiado pagado"
                     aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $porcentajePagado ?>">
                    <span style="width:<?= $porcentajePagado ?>%"></span>
                </div>
                <small>
                    <?= $porcentajePagado ?>% del monto financiado pagado ·
                    <?= Herramientas::formatearDinero($montoFinanciado) ?> financiado
                </small>
            </div>
            <div class="balance">
                <small>Saldo pendiente</small>
                <strong><?= Herramientas::formatearDinero($credito['saldo']) ?></strong>
            </div>
        </div>
        <p class="muted small">
            Ingresa un abono parcial o completa el saldo pendiente de una cuota.
            El sistema actualiza su estado y el saldo del crédito.
        </p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>N.º</th><th>Vencimiento</th><th>Monto</th>
                        <th>Pagado</th><th>Estado</th><th>Registrar pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cuotas[$credito['id']] as $cuota): ?>
                        <?php
                        $estado = $cuota['estado'];
                        if ($estado === 'Pendiente' && $cuota['fecha_vencimiento'] < date('Y-m-d')) {
                            $estado = 'Vencida';
                        }
                        $claseEstado = 'amber';
                        if ($estado === 'Pagada') {
                            $claseEstado = 'green';
                        } elseif ($estado === 'Vencida') {
                            $claseEstado = 'red';
                        }
                        $montoMaximo = min(
                            (float) $cuota['monto'] - (float) $cuota['pagado'],
                            (float) $credito['saldo']
                        );
                        ?>
                        <tr>
                            <td><?= (int) $cuota['numero'] ?></td>
                            <td><?= date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) ?></td>
                            <td><?= Herramientas::formatearDinero($cuota['monto']) ?></td>
                            <td><?= Herramientas::formatearDinero($cuota['pagado']) ?></td>
                            <td><span class="pill <?= $claseEstado ?>"><?= Herramientas::escaparHtml($estado) ?></span></td>
                            <td>
                                <?php if ($cuota['estado'] !== 'Pagada' && (float) $credito['saldo'] > 0): ?>
                                    <form method="post" class="pay-form">
                                        <?= Herramientas::campoCsrf() ?>
                                        <input type="hidden" name="action" value="registrar_pago_cuota">
                                        <input type="hidden" name="cuota_id" value="<?= (int) $cuota['id'] ?>">
                                        <input type="number" name="monto" min="0.01" step="0.01"
                                               max="<?= Herramientas::escaparHtml($montoMaximo) ?>" placeholder="Monto" required>
                                        <button type="button" class="button small-button subtle" data-fill-payment>
                                            Completar cuota
                                        </button>
                                        <button class="button small-button">Abonar</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="muted small">
            Recargo único por crédito: <?= Herramientas::escaparHtml($credito['porcentaje_recargo']) ?> % ·
            <?= Herramientas::formatearDinero($credito['importe_recargo']) ?>.
            Total en cuotas: <?= Herramientas::formatearDinero($credito['monto_financiado']) ?>.
        </p>
        <div class="payment-history">
            <div class="panel-head">
                <h3>Historial de pagos</h3>
                <small><?= count($pagosCredito[$credito['id']] ?? []) ?> movimiento(s)</small>
            </div>
            <?php if (!empty($pagosCredito[$credito['id']])): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Fecha</th><th>Concepto</th><th>Cuota</th><th>Importe</th></tr></thead>
                        <tbody>
                            <?php foreach ($pagosCredito[$credito['id']] as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                    <td><?= Herramientas::escaparHtml($pago['concepto']) ?></td>
                                    <td><?= $pago['cuota_numero'] ? '#' . (int) $pago['cuota_numero'] : 'Inicial' ?></td>
                                    <td><?= Herramientas::formatearDinero($pago['monto']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="muted small">Todavía no hay abonos registrados.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php if (!$creditos): ?>
    <section class="panel empty-state">
        <h2>Aún no hay créditos activos</h2>
        <p>Registra una venta con modalidad Crédito para generar su cronograma de cuotas.</p>
        <a class="button primary" href="index.php?page=ventas">Crear una venta a crédito</a>
    </section>
<?php endif; ?>
