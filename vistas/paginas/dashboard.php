<?php
use App\Configuracion\Herramientas;
?>
<?php
$esAdministrador = $usuarioPuede('motos.gestionar');
?>
<div class="page-head"><div><span class="eyebrow">Norcentro · <?= $esAdministrador?'Administración':'Ventas'?></span><h1><?= $esAdministrador?'Panel de administración':'Panel de vendedor'?></h1><p><?= $esAdministrador?'Gestiona el catálogo, el inventario y la operación comercial.':'Registra clientes y ventas, y atiende las solicitudes de crédito.'?></p></div><a class="button primary" href="index.php?page=ventas">+ Nueva venta</a></div>
<?php
if (($solicitudesCreditoPendientes ?? 0) > 0):?><section class="notice info credit-request-alert" role="status"><div><strong><?= (int)$solicitudesCreditoPendientes?> solicitud<?= $solicitudesCreditoPendientes === 1?'':'es'?> de compra a crédito pendiente<?= $solicitudesCreditoPendientes === 1?'':'s'?>.</strong><span>Un cliente pidió evaluar una moto y un plan de cuotas. Revisa sus datos y coordina las condiciones antes de aprobar.</span></div><a class="button primary" href="index.php?page=creditos">Revisar solicitud<?= $solicitudesCreditoPendientes === 1?'':'es'?> →</a></section><?php
endif;
?>
<div class="stats-grid">
  <?php
if ($esAdministrador):?><article class="stat-card"><span>Motocicletas</span><strong><?= (int)$resumen['motos']?></strong><small>modelos activos</small></article><?php
endif;
?><article class="stat-card"><span>Inventario</span><strong><?= (int)$resumen['disponibles']?></strong><small>unidades disponibles</small></article><article class="stat-card"><span>Clientes</span><strong><?= (int)$resumen['clientes']?></strong><small>registros activos</small></article><article class="stat-card accent"><span>Ventas</span><strong><?= (int)$resumen['ventas']?></strong><small>operaciones confirmadas</small></article><article class="stat-card"><span>Créditos activos</span><strong><?= (int)$resumen['creditos']?></strong><small>financiamientos</small></article><article class="stat-card"><span>Cuotas pendientes</span><strong><?= (int)$resumen['cuotas']?></strong><small>incluye vencidas</small></article><?php
if ($esAdministrador):?><article class="stat-card"><span>Ofertas activas</span><strong><?= (int)$resumen['ofertas']?></strong><small>dentro de su vigencia</small></article><?php
endif;
?>
</div>
<div class="notice info">Los indicadores muestran los registros guardados actualmente en la base de datos.</div>
<section class="quick-links"><?php
if ($esAdministrador):?><a href="index.php?page=motos"><b>Gestionar motocicletas</b><span>Modelos del catálogo →</span></a><a href="index.php?page=inventario"><b>Administrar inventario</b><span>Registrar unidades →</span></a><?php
endif;
?><a href="index.php?page=clientes"><b>Agregar cliente</b><span>Datos de contacto →</span></a><a href="index.php?page=creditos"><b><?= $esAdministrador?'Gestionar créditos':'Revisar créditos y cuotas'?></b><span><?= $esAdministrador?'Solicitudes y pagos':'Evaluar solicitudes y pagos'?> →</span></a><?php
if ($esAdministrador):?><a href="index.php?page=ofertas"><b>Administrar ofertas</b><span>Promociones vigentes →</span></a><?php
endif;
?></section>
