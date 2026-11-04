<?php
use App\Configuracion\Herramientas;
?>

<main class="container public-page credit-purchase">
    <div class="crumb">
        <a href="index.php?page=inicio">NORCENTRO</a> <span>/</span>
        <a href="index.php?page=catalogo">Catálogo</a> <span>/</span> Compra a crédito
    </div>

    <header class="credit-purchase-heading">
        <span class="eyebrow">Financiamiento NORCENTRO</span>
        <h1>Solicita que te contactemos</h1>
        <p>
            Un administrador o vendedor revisará tu solicitud y se comunicará contigo
            para evaluar las opciones de crédito.
        </p>
    </header>

    <div class="credit-purchase-layout">
        <section class="panel credit-purchase-summary">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Tu selección</span>
                    <h2>Motocicletas del carrito</h2>
                </div>
                <a class="text-link" href="index.php?page=catalogo">Seguir viendo motos</a>
            </div>
            <div class="credit-cart-lines" data-credit-lines aria-live="polite"></div>
            <div class="credit-cart-empty" data-credit-empty hidden>
                Tu carrito está vacío. Agrega una moto antes de solicitar el crédito.
            </div>
            <div class="credit-cart-total">
                <span>Precio referencial de las motos</span>
                <strong data-credit-total>S/ 0.00</strong>
            </div>
            <p class="credit-price-note">
                Este precio no incluye el recargo único por crédito del 5 % sobre el saldo después
                de la inicial. El asesor confirmará el precio, el recargo y las cuotas antes de registrar la venta.
            </p>
        </section>

        <section class="panel credit-application">
            <span class="eyebrow">La revisión la hace nuestro equipo</span>
            <h2>Datos para contactarte</h2>
            <div class="credit-evaluation-note">
                <strong>¿Qué pasa después?</strong>
                <p>
                    Tu solicitud llegará al panel privado de NORCENTRO con la moto y el plan que propongas.
                    Un administrador o vendedor revisará tus datos, te contactará para evaluar la inicial
                    y las cuotas y te confirmará las condiciones. Las propuestas son referenciales;
                    enviar la solicitud no significa que el crédito esté aprobado.
                </p>
            </div>

            <form id="credit-checkout" data-endpoint="api/pedidos.php" class="credit-checkout-form">
                <?= Herramientas::campoCsrf() ?>
                <label>
                    Nombres
                    <input name="nombres" autocomplete="given-name" maxlength="100" required>
                </label>
                <label>
                    Apellidos
                    <input name="apellidos" autocomplete="family-name" maxlength="100" required>
                </label>
                <label>
                    DNI
                    <input name="dni" inputmode="numeric" pattern="[0-9]{8}" maxlength="8"
                           autocomplete="off" required>
                </label>
                <label>
                    Celular
                    <input name="telefono" type="tel" autocomplete="tel" maxlength="20" required>
                </label>
                <label class="credit-field-full">
                    Correo
                    <input name="correo" type="email" autocomplete="email" maxlength="120" required>
                </label>
                <label>
                    Inicial que podrías dar (S/) <span class="muted small">Opcional y referencial</span>
                    <input name="inicial" type="number" min="0" step="0.01"
                           inputmode="decimal" placeholder="Por coordinar">
                </label>
                <label>
                    Plazo que prefieres <span class="muted small">Opcional y referencial</span>
                    <select name="cuotas">
                        <option value="">Por coordinar</option>
                        <option value="6">6 cuotas</option>
                        <option value="12">12 cuotas</option>
                        <option value="18">18 cuotas</option>
                        <option value="24">24 cuotas</option>
                        <option value="36">36 cuotas</option>
                    </select>
                </label>
                <label class="credit-consent credit-field-full">
                    <input type="checkbox" name="acepta_credito" value="1" required>
                    <span>
                        Autorizo a Norcentro a usar mi DNI y mis datos de contacto para gestionar
                        esta solicitud de crédito y comunicarse conmigo sobre su evaluación.
                    </span>
                </label>
                <p class="credit-form-feedback credit-field-full" data-credit-feedback role="status"></p>
                <button type="submit" class="button primary credit-field-full" data-credit-submit>
                    Enviar solicitud de crédito
                </button>
            </form>

            <div class="credit-request-success" data-credit-success hidden role="status">
                <span class="eyebrow">Solicitud recibida</span>
                <h2>Un asesor de NORCENTRO te contactará</h2>
                <p>
                    Registramos tu pedido de evaluación a crédito. El equipo revisará la moto y las
                    cuotas propuestas para coordinar contigo; todavía no es una aprobación.
                </p>
                <p class="credit-request-number" data-credit-success-id></p>
                <a class="button primary" href="index.php?page=catalogo">Volver al catálogo</a>
            </div>
        </section>
    </div>
</main>
