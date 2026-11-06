<?php
use App\Configuracion\Herramientas;
?>
<div class="page-head">
  <div>
    <span class="eyebrow">Operaciones</span>
    <h1>Ventas</h1>
    <p>Busca al cliente, prepara la cotización y registra la operación. El inventario se valida al confirmar.</p>
  </div>
  <a class="button subtle" href="index.php?page=creditos">Ver créditos y cuotas</a>
</div>
<div class="split-layout sales-layout">
  <section class="panel">
    <div class="panel-head">
      <h2>Nueva venta</h2>
    </div>
    <?php if ($idSolicitudCreditoVenta > 0 && $idClienteVentaSeleccionado > 0): ?>
      <div class="notice info credit-sale-notice">
        Solicitud de crédito aprobada. El cliente ya está seleccionado; confirma la unidad,
        la inicial y el cronograma antes de registrar la venta.
      </div>
    <?php endif; ?>
    <form method="get" class="sale-customer-search" role="search">
      <input type="hidden" name="page" value="ventas">
      <input type="hidden" name="cliente_id" id="sale-client-current"
             value="<?= $idClienteVentaSeleccionado > 0 ? $idClienteVentaSeleccionado : '' ?>">
      <?php if ($idSolicitudCreditoVenta > 0): ?>
        <input type="hidden" name="pedido_id" value="<?= $idSolicitudCreditoVenta ?>">
      <?php endif; ?>
      <?php if ($busquedaMoto !== ''): ?>
        <input type="hidden" name="moto_q" value="<?= Herramientas::escaparHtml($busquedaMoto) ?>">
      <?php endif; ?>
      <label for="sale-client-search">Buscar cliente por DNI, nombre o apellido</label>
      <div>
        <input id="sale-client-search" name="cliente_q"
               value="<?= Herramientas::escaparHtml($busquedaClientes) ?>"
               placeholder="Ej.: 12345678 o María Pérez" autocomplete="off">
        <button class="button small-button">Buscar</button>
      </div>
      <small>
        <?= count($listaClientes) === 20
          ? 'Mostrando hasta 20 coincidencias. Afina la búsqueda para encontrar a otra persona.'
          : 'Los resultados corresponden a clientes activos.' ?>
      </small>
    </form>
    <form method="get" class="sale-customer-search sale-unit-search" role="search">
      <input type="hidden" name="page" value="ventas">
      <input type="hidden" name="cliente_id" id="sale-bike-client"
             value="<?= $idClienteVentaSeleccionado > 0 ? $idClienteVentaSeleccionado : '' ?>">
      <?php if ($idSolicitudCreditoVenta > 0): ?>
        <input type="hidden" name="pedido_id" value="<?= $idSolicitudCreditoVenta ?>">
      <?php endif; ?>
      <?php if ($busquedaClientes !== ''): ?>
        <input type="hidden" name="cliente_q" value="<?= Herramientas::escaparHtml($busquedaClientes) ?>">
      <?php endif; ?>
      <label for="sale-unit-search">Buscar motocicleta por marca, modelo, color o serie</label>
      <div>
        <input id="sale-unit-search" name="moto_q" value="<?= Herramientas::escaparHtml($busquedaMoto) ?>"
               placeholder="Ej.: FZ25 azul o NC-55-003" autocomplete="off"
               list="sale-unit-sugerencias" data-buscador-unidades>
        <button class="button small-button">Buscar moto</button>
      </div>
      <datalist id="sale-unit-sugerencias">
        <?php foreach ($unidadesPorModelo as $grupoModelo): ?>
          <option value="<?= Herramientas::escaparHtml($grupoModelo['nombre'] . ' ' . $grupoModelo['color']) ?>">
            <?= count($grupoModelo['unidades']) ?> disponible(s)
          </option>
          <?php foreach ($grupoModelo['unidades'] as $unidadInventario): ?>
            <option value="<?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>">
              <?= Herramientas::escaparHtml($grupoModelo['nombre'] . ' · ' . $grupoModelo['color']) ?>
            </option>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </datalist>
      <small data-buscador-unidades-estado>
        <?= count($listaUnidades) === 100
          ? 'Mostrando hasta 100 unidades disponibles; presiona "Buscar moto" para buscar otras.'
          : count($listaUnidades) . ' unidad(es) disponible(s). Escribe para filtrar la lista al instante.' ?>
      </small>
    </form>
    <form method="post" class="form-grid" id="sale-form">
      <?= Herramientas::campoCsrf() ?>
      <input type="hidden" name="action" value="crear_venta">
      <?php if ($idSolicitudCreditoVenta > 0 && $idClienteVentaSeleccionado > 0): ?>
        <input type="hidden" name="pedido_id" value="<?= (int) $idSolicitudCreditoVenta ?>">
      <?php endif; ?>
      <label class="full">
        Cliente
        <select name="cliente_id" id="sale-client-select" required>
          <option value="">Selecciona entre los resultados</option>
          <?php foreach ($listaClientes as $registroCliente): ?>
            <option value="<?= (int) $registroCliente['id'] ?>"
                    data-name="<?= Herramientas::escaparHtml($registroCliente['nombres'] . ' ' . $registroCliente['apellidos']) ?>"
                    data-dni="<?= Herramientas::escaparHtml($registroCliente['dni']) ?>"
                    <?= (int) $registroCliente['id'] === $idClienteVentaSeleccionado ? 'selected' : '' ?>>
              <?= Herramientas::escaparHtml($registroCliente['nombres'] . ' ' . $registroCliente['apellidos'] . ' · DNI ' . $registroCliente['dni']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!$listaClientes): ?>
          <small>No hay coincidencias. Puedes registrar al cliente y volver con él seleccionado.</small>
        <?php endif; ?>
      </label>
      <div class="sale-inline-link full">
        <a class="text-link" href="index.php?page=clientes&amp;next=venta">
          + Registrar cliente y continuar a esta venta
        </a>
      </div>
      <label class="full">
        Unidad disponible
        <select name="unidad_id" id="unit-select" required>
          <option value="">Seleccionar motocicleta de los resultados</option>
          <?php foreach ($unidadesPorModelo as $grupoModelo): ?>
            <optgroup label="<?= Herramientas::escaparHtml(
                $grupoModelo['nombre'] . ' · ' . $grupoModelo['color']
                . ' · ' . Herramientas::formatearDinero($grupoModelo['precio'])
                . ' · ' . count($grupoModelo['unidades']) . ' disponible(s)'
            ) ?>">
              <?php foreach ($grupoModelo['unidades'] as $unidadInventario): ?>
                <option value="<?= (int) $unidadInventario['unidad_id'] ?>"
                        data-price="<?= Herramientas::escaparHtml($unidadInventario['precio']) ?>"
                        data-image="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($unidadInventario['imagen'])) ?>"
                        data-model="<?= Herramientas::escaparHtml($grupoModelo['nombre'] . ' · ' . $grupoModelo['color']) ?>"
                        data-series="<?= Herramientas::escaparHtml($unidadInventario['codigo_serie']) ?>">
                  <?= Herramientas::escaparHtml(
                      $grupoModelo['nombre'] . ' · ' . $grupoModelo['color'] . ' · Serie ' . $unidadInventario['codigo_serie']
                  ) ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <?php if (!$listaUnidades): ?>
          <small>No hay unidades disponibles para esa búsqueda. Cambia marca, modelo o serie.</small>
        <?php endif; ?>
      </label>
      <div class="selected-moto full" id="selected-moto">
        <img src="" alt="Vista previa">
        <span>Selecciona una unidad para ver su foto y precio.</span>
      </div>
      <label>
        Modalidad
        <select name="modalidad" id="sale-mode">
          <option value="Contado">Contado</option>
          <option value="Crédito" <?= $idSolicitudCreditoVenta > 0 && $idClienteVentaSeleccionado > 0 ? 'selected' : '' ?>>
            Crédito
          </option>
        </select>
      </label>
      <label>
        Descuento (S/)
        <input type="number" name="descuento" id="sale-discount" min="0" step="0.01" value="0">
      </label>
      <div class="credit-fields full" id="credit-fields">
        <label>
          Inicial (S/)
          <input name="inicial" id="sale-initial" type="number" min="0" step="0.01" value="0">
        </label>
        <label>
          Número de cuotas
          <input name="numero_cuotas" id="sale-installments" type="number" min="1" max="36" value="6">
        </label>
        <label class="full">
          Primera fecha de vencimiento
          <input name="fecha_primera_cuota" id="sale-first-due" type="date"
                 value="<?= date('Y-m-d', strtotime('+1 month')) ?>">
        </label>
      </div>
      <section class="sale-quote full" id="sale-quote" data-porcentaje-recargo="<?= (int) \App\Modelos\PlanCredito::PORCENTAJE_RECARGO ?>" aria-live="polite">
        <div class="sale-quote-heading">
          <div>
            <span class="eyebrow">Cotización de la operación</span>
            <h3>Resumen de venta</h3>
          </div>
          <span class="pill gray" id="quote-mode">Contado</span>
        </div>
        <dl class="sale-quote-lines">
          <div>
            <dt>Cliente</dt>
            <dd id="quote-client">Pendiente de seleccionar</dd>
          </div>
          <div>
            <dt>Motocicleta y serie</dt>
            <dd id="quote-unit">Pendiente de seleccionar</dd>
          </div>
          <div>
            <dt>Precio de lista</dt>
            <dd id="quote-price">S/ 0.00</dd>
          </div>
          <div>
            <dt>Descuento</dt>
            <dd id="quote-discount">− S/ 0.00</dd>
          </div>
          <div class="quote-total">
            <dt>Precio después del descuento</dt>
            <dd id="quote-total">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Inicial</dt>
            <dd id="quote-initial">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Saldo antes del recargo</dt>
            <dd id="quote-capital">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Recargo único por crédito (5 %)</dt>
            <dd id="quote-surcharge">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Total en cuotas</dt>
            <dd id="quote-financed">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Total a pagar incluida la inicial</dt>
            <dd id="quote-credit-total">S/ 0.00</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Cuotas mensuales aproximadas</dt>
            <dd id="quote-installment">—</dd>
          </div>
          <div class="quote-credit-line" hidden>
            <dt>Primera fecha</dt>
            <dd id="quote-due">—</dd>
          </div>
        </dl>
        <p class="quote-footnote" id="quote-footnote">
          El total final se confirma al registrar la venta. La unidad y el precio se validan nuevamente en el servidor.
        </p>
      </section>
      <div class="form-actions full">
        <button class="button primary" <?= !$listaUnidades ? 'disabled' : '' ?>>Confirmar venta</button>
      </div>
    </form>
    <div class="notice info">
      Al registrar una venta a crédito, el sistema genera su cronograma. Después podrás registrar pagos
      parciales o completos en “Créditos y cuotas”; cada abono actualiza el pagado de la cuota y el saldo pendiente.
    </div>
  </section>
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Historial de ventas</h2>
        <small>Las ventas a crédito tienen un enlace directo a su seguimiento.</small>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Operación</th>
            <th>Cliente</th>
            <th>Moto</th>
            <th>Total</th>
            <th>Modalidad</th>
            <th>Seguimiento</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($elementos as $ventaRegistro): ?>
            <tr>
              <td>
                <b>#<?= (int) $ventaRegistro['id'] ?></b>
                <small><?= date('d/m/Y', strtotime($ventaRegistro['fecha'])) ?></small>
              </td>
              <td><?= Herramientas::escaparHtml($ventaRegistro['nombres'] . ' ' . $ventaRegistro['apellidos']) ?></td>
              <td><?= Herramientas::escaparHtml($ventaRegistro['marca'] . ' ' . $ventaRegistro['modelo']) ?></td>
              <td><?= Herramientas::formatearDinero((float) $ventaRegistro['total'] + (float) $ventaRegistro['recargo_credito']) ?></td>
              <td>
                <span class="pill <?= $ventaRegistro['modalidad'] === 'Crédito' ? 'amber' : 'blue' ?>">
                  <?= Herramientas::escaparHtml($ventaRegistro['modalidad']) ?>
                </span>
              </td>
              <td>
                <?php if ($ventaRegistro['modalidad'] === 'Crédito'): ?>
                  <a class="text-link" href="index.php?page=creditos&amp;venta_id=<?= (int) $ventaRegistro['id'] ?>">Ver cuotas →</a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$elementos): ?>
            <tr>
              <td colspan="6" class="empty">Todavía no hay ventas registradas.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>
