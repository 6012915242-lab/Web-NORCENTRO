<?php
use App\Configuracion\Herramientas;
$formularioMoto = $editMoto ?? [];
?>
<div class="page-head"><div><span class="eyebrow">Catálogo e inventario</span><h1>Motocicletas</h1><p>Administra modelos y registra sus unidades disponibles.</p></div></div>
<div class="split-layout">
<section class="panel"><div class="panel-head"><h2><?= $editMoto?'Editar motocicleta':'Registrar motocicleta'?></h2></div><?php
if ($usuarioActual['rol'] === 'Administrador'):?><form method="post" enctype="multipart/form-data" class="form-grid"><?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="guardar_moto"><input type="hidden" name="id" value="<?= (int)($formularioMoto['id'] ?? 0)?>"><label>Marca<select name="marca_id" required><option value="">Seleccionar</option><?php
foreach ($marcas as $b):?><option value="<?= (int)$b['id']?>" <?= (int)($formularioMoto['marca_id'] ?? 0) === (int)$b['id']?'selected':''?>><?= Herramientas::escaparHtml($b['nombre'])?></option><?php
endforeach;
?></select></label><label>Modelo<input name="modelo" required value="<?= Herramientas::escaparHtml($formularioMoto['modelo'] ?? '')?>"></label><label>Cilindrada (cc)<input type="number" min="1" name="cilindrada" required value="<?= Herramientas::escaparHtml($formularioMoto['cilindrada'] ?? '')?>"></label><label>Año<input type="number" min="2000" max="2100" name="anio" required value="<?= Herramientas::escaparHtml($formularioMoto['anio'] ?? date('Y'))?>"></label><label>Color<input name="color" value="<?= Herramientas::escaparHtml($formularioMoto['color'] ?? 'Varios')?>"></label><label>Precio (S/)<input type="number" step="0.01" min="0.01" name="precio" required value="<?= Herramientas::escaparHtml($formularioMoto['precio'] ?? '')?>"></label><?php
if (!$editMoto):?><label>Unidades iniciales<input type="number" min="1" max="200" name="cantidad" required value="1"></label><?php
endif;
?><label class="full">Descripción<textarea name="descripcion" rows="2"><?= Herramientas::escaparHtml($formularioMoto['descripcion'] ?? '')?></textarea></label><label class="full">Foto (JPG/PNG/WEBP, máx. 5 MB)<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"></label><div class="form-actions full"><button class="button primary"><?= $editMoto?'Guardar cambios':'Registrar moto'?></button><?php
if ($editMoto):?><a class="button subtle" href="index.php?page=motos">Cancelar</a><?php
endif;
?></div></form><?php
else :?><p class="muted">Tu rol puede consultar el catálogo y la disponibilidad. Solo administración registra o modifica modelos.</p><?php
endif;
?></section>
<section class="panel"><div class="panel-head"><h2>Catálogo registrado</h2><form class="search-form" method="get"><input type="hidden" name="page" value="motos"><input name="q" placeholder="Buscar marca o modelo" value="<?= Herramientas::escaparHtml($_GET['q'] ?? '')?>"><button class="button small-button">Buscar</button></form></div><div class="table-wrap"><table><thead><tr><th>Moto</th><th>Precio</th><th>Disponibles</th><?php
if ($usuarioActual['rol'] === 'Administrador'):?><th>Acciones</th><?php
endif;
?></tr></thead><tbody><?php
foreach ($elementos as $moto):?><tr><td><div class="table-product"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($moto['imagen']))?>" alt=""><span><b><?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?></b><small><?= (int)$moto['cilindrada']?> cc · <?= Herramientas::escaparHtml($moto['anio'])?></small></span></div></td><td><?= Herramientas::formatearDinero($moto['precio'])?></td><td><span class="pill <?= (int)$moto['disponibles']?'green':'gray'?>"><?= (int)$moto['disponibles']?></span></td><?php
if ($usuarioActual['rol'] === 'Administrador'):?><td><div class="actions"><a href="index.php?page=motos&edit=<?= (int)$moto['id']?>">Editar</a><form method="post" onsubmit="return confirm('¿Desactivar este modelo?')"><?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="desactivar_moto"><input type="hidden" name="id" value="<?= (int)$moto['id']?>"><button class="danger-link">Desactivar</button></form></div></td><?php
endif;
?></tr><?php
endforeach;
?><?php
if (!$elementos):?><tr><td colspan="4" class="empty">No hay motocicletas para mostrar.</td></tr><?php
endif;
?></tbody></table></div></section>
</div>
