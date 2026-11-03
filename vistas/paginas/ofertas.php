<?php
use App\Configuracion\Herramientas;
?>
<main class="container public-page offers-page"><div class="crumb"><a href="index.php?page=inicio">NORCENTRO</a> <span>/</span> Motocicletas</div>
<div class="page-head offers-heading"><div><span class="eyebrow">Catálogo promocional</span><h1>Ofertas</h1><p>Encuentra tu próxima moto y revisa sus características, precio y disponibilidad.</p></div></div>
<div class="offers-toolbar"><span><strong><?= count($ofertas)?></strong> <?= count($ofertas) === 1?'modelo en oferta':'modelos en oferta'?></span><span>Promociones vigentes</span></div>
<?php
if (!empty($ofertas)):?><div class="offer-product-grid"><?php
foreach ($ofertas as $ofertaLista): $descuento = round((1 - (float)$ofertaLista['precio_oferta'] / (float)$ofertaLista['precio_normal']) * 100);
?><article class="offer-product-card"><a class="offer-product-photo" href="index.php?page=moto&amp;id=<?= (int)$ofertaLista['moto_id']?>"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($ofertaLista['imagen']))?>" alt="<?= Herramientas::escaparHtml($ofertaLista['marca'] . ' ' . $ofertaLista['modelo'])?>"><span class="offer-badge"><?= Herramientas::escaparHtml(Herramientas::textoOferta($ofertaLista['fecha_fin']))?></span></a><div class="offer-product-info"><span class="eyebrow"><?= Herramientas::escaparHtml($ofertaLista['marca'])?></span><h2><?= Herramientas::escaparHtml($ofertaLista['modelo'])?></h2><p class="product-meta"><?= (int)$ofertaLista['cilindrada']?> cc <span>·</span> Año <?= Herramientas::escaparHtml($ofertaLista['anio'])?> <span>·</span> <?= (int)$ofertaLista['disponibles']?> disponible(s)</p><div class="offer-prices"><span>Antes <?= Herramientas::formatearDinero($ofertaLista['precio_normal'])?></span><strong>Oferta <?= Herramientas::formatearDinero($ofertaLista['precio_oferta'])?></strong><small>−<?= $descuento?>%</small></div><p class="offer-expiry"><?= Herramientas::escaparHtml($ofertaLista['descripcion'])?><br>Vigente hasta <?= date('d/m/Y', strtotime($ofertaLista['fecha_fin']))?></p><a class="button primary wide" href="index.php?page=moto&amp;id=<?= (int)$ofertaLista['moto_id']?>">Ver moto</a></div></article><?php
endforeach;
?></div><?php
else :?><section class="empty-state panel"><h2>No hay ofertas que coincidan</h2><p>Vuelve pronto para ver las próximas promociones vigentes.</p><a class="button subtle" href="index.php?page=ofertas">Ver todas las ofertas</a></section><?php
endif;
?>
<?php
if ($usuarioActual && $usuarioPuede('ofertas.gestionar')):?><section class="panel offer-admin"><div class="panel-head"><div><span class="eyebrow">Administración</span><h2>Registrar una oferta</h2></div></div><form method="post" class="form-grid"><?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="guardar_oferta"><label class="full">Motocicleta<select name="moto_id" required><option value="">Seleccionar modelo</option><?php
foreach ($motosOferta as $moto):?><option value="<?= (int)$moto['id']?>"><?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?></option><?php
endforeach;
?></select></label><label>Título<input name="titulo" required placeholder="Promoción especial"></label><label>Precio normal (S/)<input type="number" min="0.01" step="0.01" name="precio_normal" required></label><label>Precio de oferta (S/)<input type="number" min="0.01" step="0.01" name="precio_oferta" required></label><label>Inicio<input type="date" name="fecha_inicio" required></label><label>Fin<input type="date" name="fecha_fin" required></label><label class="full">Descripción<textarea name="descripcion" rows="2"></textarea></label><div class="form-actions full"><button class="button primary">Publicar oferta</button></div></form><div class="table-wrap"><table><thead><tr><th>Oferta</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead><tbody><?php
foreach ($listaOfertas as $ofertaLista):?><tr><td><?= Herramientas::escaparHtml($ofertaLista['marca'] . ' ' . $ofertaLista['modelo'] . ' · ' . $ofertaLista['titulo'])?></td><td><?= date('d/m/Y', strtotime($ofertaLista['fecha_inicio']))?> – <?= date('d/m/Y', strtotime($ofertaLista['fecha_fin']))?></td><td><?= Herramientas::escaparHtml($ofertaLista['estado'])?></td><td><?php
if ($ofertaLista['estado'] === 'Activa'):?><form method="post"><?= Herramientas::campoCsrf()?><input type="hidden" name="action" value="desactivar_oferta"><input type="hidden" name="id" value="<?= (int)$ofertaLista['id']?>"><button class="danger-link">Desactivar</button></form><?php
endif;
?></td></tr><?php
endforeach;
?></tbody></table></div></section><?php
endif;
?>
<section id="contacto" class="contact-strip"><div><span class="eyebrow">¿Te interesa una promoción?</span><h2>Consulta la disponibilidad con Norcentro.</h2></div><a class="button dark" href="https://wa.me/51900286035" target="_blank" rel="noopener">Contactar por WhatsApp</a></section></main>

