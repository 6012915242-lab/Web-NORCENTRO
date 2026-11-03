<?php
use App\Configuracion\Herramientas;
$formularioCliente = $editCliente ?? [];
$puedeDesactivarCliente = $usuarioPuede('clientes.desactivar');
$continuarVenta = ($_GET['next'] ?? '') === 'venta';
?>
<div class="page-head"><div><span class="eyebrow">Atención comercial</span><h1>Clientes</h1><p>Registra y consulta datos para el seguimiento de ventas.</p></div></div>
<?php
if ($continuarVenta):?><div class="notice info">Al guardar este cliente con “Guardar y continuar a venta”, se abrirá la ficha de ventas y quedará seleccionado automáticamente.</div><?php
endif;
?>
<?php
$idClienteRegistrado = (int)($_GET['cliente_registrado'] ?? 0);
if ($idClienteRegistrado > 0):?><div class="notice info client-next-step"><span>Cliente registrado correctamente. Puedes continuar y se enviará a la siguiente ficha ya seleccionado.</span><a class="button primary" href="index.php?page=ventas&amp;cliente_id=<?= $idClienteRegistrado?>">Continuar a venta →</a></div><?php
endif;
?>
<div class="split-layout">
  <section class="panel"><div class="panel-head"><h2><?= $editCliente?'Editar cliente':'Registrar cliente'?></h2></div>
    <form method="post" class="form-grid" data-formulario-cliente>
      <?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="guardar_cliente"><input type="hidden" name="id" value="<?= (int)($formularioCliente['id'] ?? 0)?>">
      <label>DNI<input name="dni" maxlength="8" pattern="\d{8}" inputmode="numeric" required value="<?= Herramientas::escaparHtml($formularioCliente['dni'] ?? '')?>"></label>
      <div class="form-actions"><button type="button" class="button subtle" data-consultar-dni>Buscar DNI</button></div>
      <p class="full" data-mensaje-dni role="status" aria-live="polite"></p>
      <label>Nombres<input name="nombres" required value="<?= Herramientas::escaparHtml($formularioCliente['nombres'] ?? '')?>"></label>
      <label>Apellidos<input name="apellidos" required value="<?= Herramientas::escaparHtml($formularioCliente['apellidos'] ?? '')?>"></label>
      <label>Teléfono<input name="telefono" value="<?= Herramientas::escaparHtml($formularioCliente['telefono'] ?? '')?>"></label>
      <label>Correo<input type="email" name="correo" value="<?= Herramientas::escaparHtml($formularioCliente['correo'] ?? '')?>"></label>
      <label class="full">Dirección<textarea name="direccion" rows="3" maxlength="200"><?= Herramientas::escaparHtml($formularioCliente['direccion'] ?? '')?></textarea></label>
      <div class="form-actions full">
        <button class="button primary"><?= $editCliente?'Guardar cambios':'Registrar cliente'?></button>
        <?php
if (!$editCliente):?><button class="button subtle" name="continuar_venta" value="1">Guardar y continuar a venta</button><?php
endif;
?>
        <?php
if ($editCliente):?><a class="button subtle" href="index.php?page=clientes">Cancelar</a><?php
endif;
?>
      </div>
    </form>
  </section>
  <section class="panel"><div class="panel-head"><h2>Clientes registrados</h2><form class="search-form" method="get"><input type="hidden" name="page" value="clientes"><input name="q" placeholder="DNI o nombre" value="<?= Herramientas::escaparHtml($_GET['q'] ?? '')?>"><button class="button small-button">Buscar</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Cliente</th><th>DNI</th><th>Contacto</th><th>Acciones</th></tr></thead><tbody>
      <?php
foreach ($elementos as $registroCliente):?><tr><td><b><?= Herramientas::escaparHtml($registroCliente['nombres'] . ' ' . $registroCliente['apellidos'])?></b></td><td><?= Herramientas::escaparHtml($registroCliente['dni'])?></td><td><?= Herramientas::escaparHtml($registroCliente['telefono'])?><small><?= Herramientas::escaparHtml($registroCliente['correo'])?></small></td><td><div class="actions"><a href="index.php?page=ventas&amp;cliente_id=<?= (int)$registroCliente['id']?>">Nueva venta</a><a href="index.php?page=clientes&amp;edit=<?= (int)$registroCliente['id']?>">Editar</a><?php
if ($puedeDesactivarCliente):?><form method="post" onsubmit="return confirm('¿Desactivar este cliente?')"><?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="desactivar_cliente"><input type="hidden" name="id" value="<?= (int)$registroCliente['id']?>"><button class="danger-link">Desactivar</button></form><?php
endif;
?></div></td></tr><?php
endforeach;
?>
      <?php
if (!$elementos):?><tr><td colspan="4" class="empty">No hay clientes para mostrar.</td></tr><?php
endif;
?>
    </tbody></table></div>
  </section>
</div>
