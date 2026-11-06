<?php
// Estructura compartida: navegación, avisos, contenido de página y pie.
use App\Configuracion\Herramientas;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Sistema web Norcentro para motocicletas, clientes, ventas, créditos y ofertas.">
  <title><?= Herramientas::escaparHtml($titulo)?></title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/details.css?v=20261103-gallery-fix">
  <link rel="stylesheet" href="css/listing.css?v=20261106-anclas">
  <link rel="stylesheet" href="css/responsive.css?v=20261106-alineacion">
</head>
<body class="page-<?= Herramientas::escaparHtml($pagina)?><?= $mensajeError ? ' page-error' : ''?>">
<?php
$mostrarCarrito = !$usuarioActual || in_array($pagina, ['inicio',
        'catalogo',
        'ofertas',
        'moto'], true);
?>
<header class="topbar">
  <a class="brand" href="index.php?page=inicio" aria-label="NORCENTRO, inicio">
    <img class="brand-logo" src="img/brand/norcentro_logo_completo.jpg" alt="NORCENTRO · El poder en movimiento">
  </a>
  <nav class="main-nav">
    <a href="index.php?page=inicio">Inicio</a>
    <a href="index.php?page=catalogo">Catálogo</a>
    <a href="index.php?page=ofertas">Ofertas</a>
    <a href="index.php?page=contacto">Contacto</a>
  </nav>
  <div class="top-action">
    <?php if ($mostrarCarrito): ?>
      <button type="button" class="cart-trigger" id="cart-toggle" aria-haspopup="dialog">
        Carrito <span data-cart-count>0</span>
      </button>
    <?php endif; ?>
    <?php if ($usuarioActual): ?>
      <a href="index.php?page=dashboard">Panel</a>
      <span class="user-chip">
        <?= Herramientas::escaparHtml($usuarioActual['nombre']) ?> ·
        <?= Herramientas::escaparHtml($usuarioActual['rol']) ?>
      </span>
      <form method="post" class="inline-form">
        <?= Herramientas::campoCsrf() ?>
        <input type="hidden" name="action" value="cerrar_sesion">
        <button class="link-button">Salir</button>
      </form>
    <?php else: ?>
      <a href="index.php?page=login">Iniciar sesión</a>
    <?php endif; ?>
  </div>
</header>
<?php
if ($mostrarCarrito) {
    require __DIR__ . '/carrito.php';
}
?>
<?php if ($mensajeError): ?>
  <main class="container page-error-message">
    <div class="notice error">
      <strong>No se pudo cargar la aplicación.</strong><br>
      <?= Herramientas::escaparHtml($mensajeError) ?>
    </div>
  </main>
<?php else: ?>
<?php if ($aviso): ?>
  <div class="toast <?= Herramientas::escaparHtml($aviso[0]) ?>" role="status">
    <?= Herramientas::escaparHtml($aviso[1]) ?>
  </div>
<?php endif; ?>
<?php
$paginasSinPanelLateral = [
    'inicio',
    'catalogo',
    'ofertas',
    'contacto',
    'login',
    'moto',
    'compra',
    'compra_credito',
    'acceso_denegado',
];
if ($usuarioActual && !in_array($pagina, $paginasSinPanelLateral, true)):?>
<div class="app-shell">
  <aside class="sidebar">
    <div class="side-label">Área privada · <?= Herramientas::escaparHtml($usuarioActual['rol']) ?></div>
    <a href="index.php?page=dashboard" class="<?= $pagina === 'dashboard' ? 'active' : '' ?>">
      <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
        <rect x="3" y="3" width="8" height="8" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/>
        <rect x="14" y="11" width="7" height="10" rx="1"/><rect x="3" y="14" width="8" height="7" rx="1"/>
      </svg></span><span>Mi panel</span>
    </a>
    <?php if ($usuarioPuede('motos.gestionar')): ?>
      <a href="index.php?page=motos" class="<?= $pagina === 'motos' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/>
          <path d="m6 17 4-7h4l4 7M9 13h7m-5-3-2-3H6m9 6 2-3h2"/>
        </svg></span><span>Gestionar motos</span>
      </a>
    <?php endif; ?>
    <?php if ($usuarioPuede('clientes.gestionar')): ?>
      <a href="index.php?page=clientes" class="<?= $pagina === 'clientes' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <circle cx="9" cy="8" r="3"/><path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20zM16 5.5a3 3 0 0 1 0 5.8M17 14a5 5 0 0 1 3.5 4.8V20"/>
        </svg></span><span>Clientes</span>
      </a>
    <?php endif; ?>
    <?php if ($usuarioPuede('inventario.gestionar')): ?>
      <a href="index.php?page=inventario" class="<?= $pagina === 'inventario' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <path d="m3 7 9-4 9 4-9 4zM3 7v10l9 4 9-4V7M12 11v10M7.5 5l9 4"/>
        </svg></span><span>Inventario</span>
      </a>
    <?php endif; ?>
    <?php if ($usuarioPuede('ventas.registrar')): ?>
      <a href="index.php?page=ventas" class="<?= $pagina === 'ventas' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <path d="M4 19V5m0 14h17M7 15l4-4 3 2 6-7"/><path d="M16 6h4v4"/>
        </svg></span><span>Ventas</span>
      </a>
    <?php endif; ?>
    <?php if ($usuarioPuede('creditos.revisar')): ?>
      <a href="index.php?page=creditos" class="<?= $pagina === 'creditos' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>
        </svg></span><span>Créditos y cuotas</span>
        <?php if (($solicitudesCreditoPendientes ?? 0) > 0): ?>
          <span class="sidebar-count" aria-label="<?= (int) $solicitudesCreditoPendientes ?> solicitudes por revisar">
            <?= (int) $solicitudesCreditoPendientes ?>
          </span>
        <?php endif; ?>
      </a>
    <?php endif; ?>
    <?php if ($usuarioPuede('ofertas.gestionar')): ?>
      <a href="index.php?page=ofertas" class="<?= $pagina === 'ofertas' ? 'active' : '' ?>">
        <span class="side-icon" aria-hidden="true"><svg viewBox="0 0 24 24">
          <path d="M20 13 12 21 3 12V4h8z"/><circle cx="7.5" cy="8" r="1"/>
        </svg></span><span>Gestionar ofertas</span>
      </a>
    <?php endif; ?>
    <div class="side-foot"><?= $usuarioPuede('motos.gestionar')?'Administración completa':'Atención y ventas'?> · NORCENTRO</div>
  </aside>
  <main class="workspace">
    <?php require $vista; ?>
  </main>
</div>
<?php else: ?>
  <?php require $vista; ?>
<?php endif; ?>
<?php endif; ?>
<footer class="footer">
  <span>Norcentro S.A.C.</span>
  <span>Precios y disponibilidad sujetos a confirmación.</span>
</footer>
<script src="js/app.js?v=20261106-editar-unidad" defer></script>
<script src="js/cart.js?v=20260929-credit-review" defer></script>
<?php if ($pagina === 'compra'): ?>
  <script src="js/order-status.js?v=20260927-mp" defer></script>
<?php endif; ?>
<?php if ($pagina === 'compra_credito'): ?>
  <script src="js/credit-checkout.js?v=20260929-credit-review" defer></script>
<?php endif; ?>
<?php if ($pagina === 'motos'): ?>
  <script src="js/admin-motos.js?v=20261106-unidades-color" defer></script>
<?php endif; ?>
<?php if ($pagina === 'clientes'): ?>
  <script src="js/consulta-dni.js" defer></script>
<?php endif; ?>
<?php if ($pagina === 'inventario'): ?>
  <script src="js/inventario.js?v=20261106-ajax" defer></script>
<?php endif; ?>
</body>
</html>
