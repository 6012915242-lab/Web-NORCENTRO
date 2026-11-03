<?php
use App\Configuracion\Herramientas;
?>
<main class="container public-page catalog-page">
  <div class="crumb"><a href="index.php?page=inicio">NORCENTRO</a> <span>/</span> Catálogo</div>
  <section class="catalog-intro"><div><span class="eyebrow">Motos Norcentro</span><h1>Encuentra tu próxima moto.</h1><p>Compara modelos, revisa los colores disponibles y consulta la ficha de cada moto.</p></div><a class="text-link" href="index.php?page=ofertas">Ver ofertas →</a></section>
  <form class="catalog-filters" method="get" action="index.php"><input type="hidden" name="page" value="catalogo"><label>Marca<select name="marca"><option value="0">Todas las marcas</option><?php
foreach ($marcasCatalogo as $idMarca => $nombreMarca):?><option value="<?= (int)$idMarca?>" <?= $marcaSeleccionada === (int)$idMarca?'selected':''?>><?= Herramientas::escaparHtml($nombreMarca)?></option><?php
endforeach;
?></select></label><label>Modelo<select name="modelo"><option value="">Todos los modelos</option><?php
foreach ($modelosCatalogo as $modelo):?><option value="<?= Herramientas::escaparHtml($modelo)?>" <?= $modeloSeleccionado === $modelo?'selected':''?>><?= Herramientas::escaparHtml($modelo)?></option><?php
endforeach;
?></select></label><label>Cilindrada<select name="cilindrada"><option value="0">Todas</option><?php
foreach ($cilindradasCatalogo as $cc):?><option value="<?= (int)$cc?>" <?= $cilindradaSeleccionada === $cc?'selected':''?>><?= (int)$cc?> cc</option><?php
endforeach;
?></select></label><label>Precio máximo (S/)<input type="number" min="0" step="500" name="precio_max" value="<?= $precioMaximo > 0?Herramientas::escaparHtml((string)(int)$precioMaximo):''?>" placeholder="Sin límite"></label><div class="filter-action"><button class="button primary">Aplicar filtros</button><a href="index.php?page=catalogo">Limpiar</a></div></form>
  <p class="catalog-result-count"><?= count($catalogo)?> <?= count($catalogo) === 1?'modelo encontrado':'modelos encontrados'?></p>
  <div class="product-grid">
  <?php
foreach ($catalogo as $moto): $coloresMoto = $variantesColorCatalogo[(int)$moto['id']] ?? [];
$primerColor = $coloresMoto[0] ?? null;
$ofertaEnTarjeta = null;
foreach ($ofertas as $oferta) if ((int)$oferta['moto_id'] === (int)$moto['id']) {
    $ofertaEnTarjeta = $oferta;
    break;
}
$precioTarjeta = (float)($ofertaEnTarjeta['precio_oferta'] ?? $moto['precio']);
?><article class="product-card"><a class="product-image" href="index.php?page=moto&amp;id=<?= (int)$moto['id']?>"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($moto['imagen']))?>" alt="<?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?>"></a><div class="product-info"><span class="eyebrow"><?= Herramientas::escaparHtml($moto['marca'])?></span><h3><a class="product-title" href="index.php?page=moto&amp;id=<?= (int)$moto['id']?>"><?= Herramientas::escaparHtml($moto['modelo'])?></a></h3><p><?= (int)$moto['cilindrada']?> cc · <?= Herramientas::escaparHtml($moto['anio'])?></p><p class="product-description"><?= Herramientas::escaparHtml($moto['descripcion'])?></p><?php
if ($coloresMoto):?><div class="catalog-color-row"><span>Colores</span><?php
foreach ($coloresMoto as $variante):?><a class="catalog-color-swatch" href="index.php?page=moto&amp;id=<?= (int)$moto['id']?>&amp;color=<?= (int)$variante['id']?>" style="--swatch-color:<?= Herramientas::escaparHtml($variante['codigo_hex'])?>" title="<?= Herramientas::escaparHtml($variante['nombre'])?>" aria-label="Ver <?= Herramientas::escaparHtml($variante['nombre'])?>"></a><?php
endforeach;
?><a class="catalog-color-more" href="index.php?page=moto&amp;id=<?= (int)$moto['id']?>">Ver colores</a></div><?php
endif;
?><div class="product-bottom"><?php
if ($ofertaEnTarjeta):?><s><?= Herramientas::formatearDinero($ofertaEnTarjeta['precio_normal'])?></s><?php
endif;
?><strong><?= Herramientas::formatearDinero($precioTarjeta)?></strong><span><?= (int)$moto['disponibles']?> disponibles</span></div><a class="text-link detail-link" href="index.php?page=moto&amp;id=<?= (int)$moto['id']?>">Ver ficha →</a><button type="button" class="button primary add-to-cart" data-id="<?= (int)$moto['id']?>" data-name="<?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?>" data-price="<?= Herramientas::escaparHtml(number_format($precioTarjeta, 2, '.', ''))?>" data-color-id="<?= (int)($primerColor['id'] ?? 0)?>" data-color="<?= Herramientas::escaparHtml($primerColor['nombre'] ?? $moto['color'])?>" data-image="<?= Herramientas::escaparHtml(Herramientas::rutaImagen($moto['imagen']))?>" <?= (int)$moto['disponibles'] < 1?'disabled':''?>>Agregar al carrito</button></div></article><?php
endforeach;
?>
  <?php
if (!$catalogo):?><div class="empty-state panel"><p>No encontramos motos con esos filtros.</p><a class="button subtle" href="index.php?page=catalogo">Mostrar todo el catálogo</a></div><?php
endif;
?>
  </div>
</main>
