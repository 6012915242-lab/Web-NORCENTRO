<?php
use App\Configuracion\Herramientas;
if (!$motoDetalle):?>
<main class="container public-page"><section class="empty-state panel"><h1>No encontramos esta motocicleta</h1><p>Es posible que ya no esté disponible en el catálogo.</p><a class="button primary" href="index.php?page=catalogo">Volver al catálogo</a></section></main>
<?php
else : $precio = $ofertaMotoDetalle?(float)$ofertaMotoDetalle['precio_oferta']:(float)$motoDetalle['precio'];
$imagenesGaleria = $imagenesMoto;
$indiceVistaActiva = 0;
$fotoVistaActiva = $imagenesGaleria[$indiceVistaActiva] ?? ['ruta' => $motoDetalle['imagen'], 'texto_alt' => $motoDetalle['marca'] . ' ' . $motoDetalle['modelo']];
$etiquetaVistaActiva = trim((string)($fotoVistaActiva['texto_alt'] ?? '')) ?: 'Vista 1 de la motocicleta';
?>
<main class="container public-page detail-page"><div class="detail-topbar"><a class="button subtle detail-back" href="index.php?page=catalogo">← Volver al catálogo</a><div class="crumb"><a href="index.php?page=inicio">NORCENTRO</a> <span>/</span> <a href="index.php?page=catalogo">Catálogo</a> <span>/</span> <?= Herramientas::escaparHtml($motoDetalle['marca'] . ' ' . $motoDetalle['modelo'])?></div></div>
<section class="product-detail"><div>
<?php
if (!empty($variantesColor)):?><div class="moto-color-picker"><strong>Colores</strong><div class="moto-color-options"><?php
foreach ($variantesColor as $variante): $estaSeleccionado = (int)$variante['id'] === (int)$colorSeleccionado['id'];
?><a class="moto-color-swatch <?= $estaSeleccionado?'active':''?>" href="index.php?page=moto&amp;id=<?= (int)$motoDetalle['id']?>&amp;color=<?= (int)$variante['id']?>" data-color-variant="<?= (int)$variante['id']?>" style="--swatch-color:<?= Herramientas::escaparHtml($variante['codigo_hex'])?>" aria-label="Ver color <?= Herramientas::escaparHtml($variante['nombre'])?>" <?= $estaSeleccionado?'aria-current="true"':''?> title="<?= Herramientas::escaparHtml($variante['nombre'])?>"></a><?php
endforeach;
?></div><span id="selected-color-name"><?= Herramientas::escaparHtml($colorSeleccionado['nombre'])?></span></div><?php
endif;
?>
<div class="detail-gallery"><div class="detail-thumbnails">
<?php
foreach ($imagenesGaleria as $i => $foto): $etiquetaFoto = trim((string)($foto['texto_alt'] ?? '')) ?: 'Vista ' . ($i + 1) . ' de la motocicleta';?><button class="detail-thumb <?= $i === $indiceVistaActiva?'active':''?>" type="button" <?= !empty($colorSeleccionado)?'data-color-group="' . (int)$colorSeleccionado['id'] . '"':''?> data-gallery-src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($foto['ruta']))?>" data-gallery-alt="<?= Herramientas::escaparHtml($etiquetaFoto)?>" data-gallery-label="<?= Herramientas::escaparHtml($etiquetaFoto)?>" aria-label="Ver <?= Herramientas::escaparHtml($etiquetaFoto)?>" aria-pressed="<?= $i === $indiceVistaActiva?'true':'false'?>" title="<?= Herramientas::escaparHtml($etiquetaFoto)?>"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($foto['ruta']))?>" alt=""><span>Vista <?= $i + 1?></span></button><?php
endforeach;
?>
?></div><div class="detail-image"><img id="product-main-image" src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($fotoVistaActiva['ruta']))?>" alt="<?= Herramientas::escaparHtml($etiquetaVistaActiva)?>" tabindex="0" aria-describedby="gallery-caption"><?php
if (count($imagenesMoto) > 1):?><button class="detail-gallery-arrow detail-gallery-prev" type="button" data-gallery-prev aria-label="Ver foto anterior"><span aria-hidden="true">‹</span></button><button class="detail-gallery-arrow detail-gallery-next" type="button" data-gallery-next aria-label="Ver foto siguiente"><span aria-hidden="true">›</span></button><span class="detail-gallery-counter" data-gallery-counter aria-live="polite">1 / <?= count($imagenesMoto)?></span><?php
endif;
?><?php
if ($ofertaMotoDetalle):?><span class="offer-badge"><?= Herramientas::escaparHtml(Herramientas::textoOferta($ofertaMotoDetalle['fecha_fin']))?></span><?php
endif;
?></div><p class="detail-gallery-caption" id="gallery-caption" data-gallery-caption aria-live="polite"><?= Herramientas::escaparHtml($etiquetaVistaActiva)?></p></div></div>
</div><div class="detail-copy"><span class="eyebrow"><?= Herramientas::escaparHtml($motoDetalle['marca'])?></span><h1><?= Herramientas::escaparHtml($motoDetalle['modelo'])?></h1><p class="detail-sku">Código de catálogo: NC-MOTO-<?= (int)$motoDetalle['id']?></p><div class="availability <?= (int)$motoDetalle['disponibles'] > 0?'available':'unavailable'?>"><span></span><?= (int)$motoDetalle['disponibles'] > 0?'En stock · ' . (int)$motoDetalle['disponibles'] . ' unidad(es) disponible(s)':'Consulta disponibilidad'?></div>
<?php
if ($ofertaMotoDetalle):?><div class="detail-offer-title"><strong><?= Herramientas::escaparHtml($ofertaMotoDetalle['titulo'])?></strong><span><?= Herramientas::escaparHtml($ofertaMotoDetalle['descripcion'])?></span></div><?php
endif;
?><div class="detail-price"><?php
if ($ofertaMotoDetalle):?><s><?= Herramientas::formatearDinero($ofertaMotoDetalle['precio_normal'])?></s><?php
endif;
?><strong><?= Herramientas::formatearDinero($precio)?></strong><?php
if ($ofertaMotoDetalle):?><span class="discount">−<?= round((1 - $precio / (float)$ofertaMotoDetalle['precio_normal']) * 100)?>%</span><?php
endif;
?></div>
<?php
if ($ofertaMotoDetalle):?><p class="detail-validity">Promoción válida hasta el <?= date('d/m/Y', strtotime($ofertaMotoDetalle['fecha_fin']))?>. Sujeta a disponibilidad de unidades.</p><?php
endif;
?>
<div class="detail-purchase-actions"><button type="button" class="button primary add-to-cart" data-id="<?= (int)$motoDetalle['id']?>" data-name="<?= Herramientas::escaparHtml($motoDetalle['marca'] . ' ' . $motoDetalle['modelo'])?>" data-price="<?= Herramientas::escaparHtml(number_format($precio, 2, '.', ''))?>" data-color-id="<?= (int)($colorSeleccionado['id'] ?? 0)?>" data-color="<?= Herramientas::escaparHtml($colorSeleccionado['nombre'] ?? $motoDetalle['color'])?>" data-image="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($imagenesMoto[0]['ruta']))?>" <?= (int)$motoDetalle['disponibles'] < 1?'disabled':''?>>Agregar al carrito</button><button type="button" class="button subtle buy-on-credit" data-id="<?= (int)$motoDetalle['id']?>" data-name="<?= Herramientas::escaparHtml($motoDetalle['marca'] . ' ' . $motoDetalle['modelo'])?>" data-price="<?= Herramientas::escaparHtml(number_format($precio, 2, '.', ''))?>" data-color-id="<?= (int)($colorSeleccionado['id'] ?? 0)?>" data-color="<?= Herramientas::escaparHtml($colorSeleccionado['nombre'] ?? $motoDetalle['color'])?>" data-image="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($imagenesMoto[0]['ruta']))?>" <?= (int)$motoDetalle['disponibles'] < 1?'disabled':''?>>Comprar a crédito</button><a class="button subtle detail-whatsapp" href="https://wa.me/51900286035?text=<?= rawurlencode('Hola, tengo una consulta sobre ' . $motoDetalle['marca'] . ' ' . $motoDetalle['modelo'] . '.')?>" target="_blank" rel="noopener">Consultar por WhatsApp ↗</a></div><p class="detail-note">Compra al contado con Mercado Pago o solicita una evaluación de crédito. La aprobación se confirma con Norcentro.</p>
</div></section>
<section class="panel specifications"><span class="eyebrow">Información del modelo</span><h2>Características de la motocicleta</h2><p><?= Herramientas::escaparHtml($motoDetalle['descripcion'])?></p><dl><div><dt>Marca</dt><dd><?= Herramientas::escaparHtml($motoDetalle['marca'])?></dd></div><div><dt>Modelo</dt><dd><?= Herramientas::escaparHtml($motoDetalle['modelo'])?></dd></div><div><dt>Cilindrada</dt><dd><?= (int)$motoDetalle['cilindrada']?> cc</dd></div><div><dt>Año</dt><dd><?= Herramientas::escaparHtml($motoDetalle['anio'])?></dd></div><div><dt>Color</dt><dd id="selected-color-detail"><?= Herramientas::escaparHtml($colorSeleccionado['nombre'] ?? $motoDetalle['color'])?></dd></div><div><dt>Disponibilidad</dt><dd><?= (int)$motoDetalle['disponibles']?> unidad(es)</dd></div></dl><?php
if (!empty($especificacionesMoto)):?><div class="specification-table"><h3>Datos de referencia</h3><div class="table-wrap"><table><thead><tr><th>Grupo</th><th>Característica</th><th>Detalle</th></tr></thead><tbody><?php
foreach ($especificacionesMoto as $especificacion):?><tr><td><?= Herramientas::escaparHtml($especificacion['grupo'])?></td><td><?= Herramientas::escaparHtml($especificacion['nombre'])?></td><td><?= Herramientas::escaparHtml($especificacion['valor'])?></td></tr><?php
endforeach;
?></tbody></table></div><small>Precios y disponibilidad deben confirmarse con Norcentro.</small></div><?php
endif;
?></section>
<a class="text-link back-link" href="index.php?page=catalogo">← Volver al catálogo</a></main>
<?php
endif;
?>

