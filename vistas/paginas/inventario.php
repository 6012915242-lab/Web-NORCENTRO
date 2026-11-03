<?php
use App\Configuracion\Herramientas;
?>
<div class="page-head"><div><span class="eyebrow">Unidades físicas</span><h1>Inventario</h1><p>Registra cada unidad por su código de serie y consulta su estado actual.</p></div><span class="inventory-total"><?= count($elementos)?> unidades registradas</span></div>

<?php
if ($usuarioPuede('motos.gestionar')):?>
<section class="panel inventory-register">
  <div class="panel-head"><div><h2>Registrar unidad</h2><p class="muted">La unidad se guardará en la base de datos como disponible.</p></div></div>
  <form method="post" class="form-grid">
    <?= Herramientas::campoCsrf()?>
    <input type="hidden" name="action" value="agregar_unidad_inventario">
    <label>Motocicleta<select name="moto_id" required><option value="">Seleccionar modelo</option><?php
foreach ($motosInventario as $moto):?><option value="<?= (int)$moto['id']?>"><?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?></option><?php
endforeach;
?></select></label>
    <label>Código de serie<input name="codigo_serie" maxlength="50" minlength="3" pattern="[A-Za-z0-9][A-Za-z0-9-]{2,49}" autocomplete="off" required><small>Usa el número de serie de la unidad.</small></label>
    <div class="form-actions full"><button class="button primary">Guardar en inventario</button></div>
  </form>
</section>
<?php
endif;
?>

<section class="panel"><div class="table-wrap"><table><thead><tr><th>Motocicleta</th><th>Código de serie</th><th>Estado</th></tr></thead><tbody>
<?php
foreach ($elementos as $unidadInventario):?><tr><td><div class="table-product"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($unidadInventario['imagen']))?>" alt=""><b><?= Herramientas::escaparHtml($unidadInventario['marca'] . ' ' . $unidadInventario['modelo'])?></b></div></td><td><?= Herramientas::escaparHtml($unidadInventario['codigo_serie'])?></td><td><span class="pill <?= $unidadInventario['estado'] === 'Disponible'?'green':($unidadInventario['estado'] === 'Vendida'?'red':'amber')?>"><?= Herramientas::escaparHtml($unidadInventario['estado'])?></span></td></tr><?php
endforeach;
?>
<?php
if (!$elementos):?><tr><td colspan="3" class="empty">Aún no hay unidades registradas.</td></tr><?php
endif;
?>
</tbody></table></div><p class="muted small inventory-note">Las ventas y los pedidos pagados actualizan el estado de cada unidad automáticamente.</p></section>
