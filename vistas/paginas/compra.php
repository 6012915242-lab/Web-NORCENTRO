<?php
use App\Configuracion\Herramientas;

$textoCompra = match ($pedidoCompra['estado'] ?? '') {
    'Pagado' => 'Pago aprobado. Tu compra quedó confirmada.',
    'En revisión de crédito' => 'Recibimos tu solicitud de crédito. Norcentro revisará las condiciones y te contactará.',
    'Cancelado' => 'El pedido se canceló o venció. Puedes volver al catálogo e intentarlo otra vez.',
    'Error de pago' => 'No se pudo iniciar el pago. Vuelve al catálogo y crea un nuevo pedido.',
    default => 'Estamos verificando el resultado del pago con Mercado Pago.',
};
?>

<main class="container public-page checkout-result"
      data-request-id="<?= Herramientas::escaparHtml($pedidoCompra['request_id'] ?? '') ?>"
      data-mode="<?= Herramientas::escaparHtml($pedidoCompra['modalidad'] ?? '') ?>">
    <section class="panel checkout-result-card">
        <span class="eyebrow">NORCENTRO · Compra</span>
        <h1><?= !empty($pedidoCompra) ? 'Estado de tu pedido' : 'No encontramos este pedido' ?></h1>

        <?php if (!empty($pedidoCompra)): ?>
            <p class="checkout-result-message" data-order-message>
                <?= Herramientas::escaparHtml($textoCompra) ?>
            </p>
            <div class="checkout-result-meta">
                <span>Pedido #<?= (int) $pedidoCompra['id'] ?></span>
                <strong><?= Herramientas::formatearDinero($pedidoCompra['total']) ?></strong>
                <span class="pill amber" data-order-status>
                    <?= Herramientas::escaparHtml($pedidoCompra['estado']) ?>
                </span>
            </div>
            <p class="checkout-result-help">
                <?php if (($pedidoCompra['modalidad'] ?? '') === 'Crédito'): ?>
                    La solicitud no representa una aprobación automática.
                    Norcentro revisará la información y se comunicará contigo.
                <?php else: ?>
                    Si acabas de pagar, el estado puede tardar unos segundos en actualizarse.
                    No compartas los datos de tu tarjeta por WhatsApp.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <p>El enlace de confirmación no es válido o ya no está disponible.</p>
        <?php endif; ?>

        <div class="checkout-result-actions">
            <a class="button primary" href="index.php?page=catalogo">Volver al catálogo</a>
            <a class="button subtle"
               href="https://wa.me/51900286035?text=<?= rawurlencode('Hola, necesito ayuda con el estado de mi pedido Norcentro.') ?>"
               target="_blank" rel="noopener">Ayuda por WhatsApp ↗</a>
        </div>
    </section>
</main>
