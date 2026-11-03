<?php
use App\Configuracion\Herramientas;
?>
<main class="home-page">
  <section class="home-hero" aria-labelledby="home-title">
    <?php
if ($motoDestacada):?>
      <article class="home-slide cover-slide active">
        <div class="home-hero-copy">
          <span class="home-kicker">NORCENTRO <i></i> EL PODER EN MOVIMIENTO</span>
          <h1 id="home-title">Elige tu moto. Marca tu ruta.</h1>
          <p>Explora modelos Bajaj, Yamaha, Suzuki y KTM; compara cada opción y cotiza con el equipo Norcentro.</p>
          <div class="home-hero-actions">
            <a class="button primary" href="index.php?page=catalogo">Explorar catálogo</a>
            <a class="home-hero-link" href="<?= (int)$motoDestacada['id'] > 0 ? 'index.php?page=moto&amp;id=' . (int)$motoDestacada['id'] : 'index.php?page=catalogo'?>">Ver moto destacada <span aria-hidden="true">↗</span></a>
          </div>
          <div class="home-hero-note"><span></span><strong><?= Herramientas::escaparHtml($motoDestacada['marca'] . ' ' . $motoDestacada['modelo'])?></strong><i></i><?= (int)$motoDestacada['cilindrada']?> cc <i></i><?= Herramientas::formatearDinero($motoDestacada['precio'])?></div>
        </div>
      </article>
    <?php
else :?>
      <div class="home-hero-copy"><span class="home-kicker">NORCENTRO <i></i> EL PODER EN MOVIMIENTO</span><h1 id="home-title">Elige tu moto. Marca tu ruta.</h1><p>Explora modelos Bajaj, Yamaha, Suzuki y KTM; compara cada opción y cotiza con el equipo Norcentro.</p><a class="button primary" href="index.php?page=catalogo">Explorar catálogo</a></div>
    <?php
endif;
?>
  </section>

  <?php
if ($motosDestacadas):?>
  <section class="home-highlights" aria-labelledby="home-featured-title">
    <header class="home-highlights-heading"><div><span class="eyebrow">Una selección para salir a rodar</span><h2 id="home-featured-title">Modelos destacados</h2></div><a class="text-link" href="index.php?page=catalogo">Ver catálogo completo <span aria-hidden="true">→</span></a></header>
    <div class="home-highlight-grid">
      <?php
foreach ($motosDestacadas as $moto): $urlMoto = (int)$moto['id'] > 0?'index.php?page=moto&amp;id=' . (int)$moto['id']:'index.php?page=catalogo';
$textoDestacado = match($moto['modelo']) {
    'YZF-R15 ABS' => 'VVA · ABS · control de tracción · Y-Connect', 'MT-09 ABS' => '888 cc · ABS · tablero TFT · conectividad', 'Pulsar NS200' => 'Inyección electrónica · ABS · iluminación LED', default => (int)$moto['cilindrada'] . ' cc · Modelo destacado Norcentro.'
}
;
?>
      <article class="home-highlight-card">
        <a class="home-highlight-image" href="<?= $urlMoto?>"><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen((string)$moto['imagen']))?>" alt="<?= Herramientas::escaparHtml($moto['marca'] . ' ' . $moto['modelo'])?>"></a>
        <div class="home-highlight-copy"><span class="home-highlight-brand"><?= Herramientas::escaparHtml($moto['marca'])?></span><h3><?= Herramientas::escaparHtml($moto['modelo'])?></h3><p><?= Herramientas::escaparHtml($textoDestacado)?></p><div class="home-highlight-bottom"><strong><?= Herramientas::formatearDinero($moto['precio'])?></strong><a href="<?= $urlMoto?>">Ver moto <span aria-hidden="true">↗</span></a></div></div>
      </article>
      <?php
endforeach;
?>
    </div>
  </section>
  <?php
endif;
?>

  <?php
if ($ofertaDestacada): $precioNormalOferta = (float)$ofertaDestacada['precio_normal'];
$precioOferta = (float)$ofertaDestacada['precio_oferta'];
$ahorroOferta = $precioNormalOferta - $precioOferta;
$descuentoOferta = (int)round(($ahorroOferta / $precioNormalOferta) * 100);
$urlOferta = (int)$ofertaDestacada['moto_id'] > 0?'index.php?page=moto&amp;id=' . (int)$ofertaDestacada['moto_id']:'index.php?page=catalogo';
?>
  <dialog class="home-promo-dialog" id="home-promo-dialog" data-promo-id="<?= (int)$ofertaDestacada['id']?>" aria-labelledby="home-promo-title">
    <article class="home-promo-card">
      <button class="home-promo-close" type="button" data-home-promo-dismiss aria-label="Cerrar promoción">×</button>
      <div class="home-promo-photo"><span class="home-promo-discount">−<?= $descuentoOferta?>%</span><img src="<?= Herramientas::escaparHtml(Herramientas::rutaImagen((string)$ofertaDestacada['imagen']))?>" alt="<?= Herramientas::escaparHtml($ofertaDestacada['marca'] . ' ' . $ofertaDestacada['modelo'])?>"></div>
      <div class="home-promo-copy"><span class="eyebrow">Oferta destacada · Norcentro</span><h2 id="home-promo-title"><?= Herramientas::escaparHtml($ofertaDestacada['marca'] . ' ' . $ofertaDestacada['modelo'])?></h2><p class="home-promo-lead">Aprovecha el mayor descuento disponible.</p><div class="home-promo-prices"><strong><?= Herramientas::formatearDinero($precioOferta)?></strong><s><?= Herramientas::formatearDinero($precioNormalOferta)?></s></div><p class="home-promo-saving">Ahorras <?= Herramientas::formatearDinero($ahorroOferta)?></p><p class="home-promo-validity"><?= Herramientas::escaparHtml(Herramientas::textoOferta((string)$ofertaDestacada['fecha_fin']))?> · Vigente hasta <?= date('d/m/Y', strtotime((string)$ofertaDestacada['fecha_fin']))?>. Sujeto a disponibilidad.</p><a class="button primary home-promo-cta" href="<?= $urlOferta?>">Ver esta moto <span aria-hidden="true">↗</span></a><button class="home-promo-later" type="button" data-home-promo-dismiss>Seguir viendo Inicio</button></div>
    </article>
  </dialog>
  <?php
endif;
?>
</main>
