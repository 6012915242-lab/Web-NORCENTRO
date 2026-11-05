<?php
use App\Configuracion\Herramientas;
?>
<dialog class="cart-dialog" id="cart-dialog" aria-labelledby="cart-heading">
    <section class="cart-panel">
        <div class="cart-heading">
            <div>
                <span class="eyebrow">Compra en línea</span>
                <h2 id="cart-heading">Tu carrito</h2>
            </div>
            <button type="button" class="cart-close" id="cart-close" aria-label="Cerrar carrito">×</button>
        </div>
        <div class="cart-lines" data-cart-lines></div>
        <div class="cart-empty" data-cart-empty>
            Tu carrito está vacío. Agrega una moto del catálogo para empezar.
        </div>
        <div class="cart-total">
            <span>Total referencial</span>
            <strong data-cart-total>S/ 0.00</strong>
        </div>
        <button type="button" class="button primary cart-checkout-start" data-cart-start disabled>
            Continuar compra
        </button>
        <a class="cart-help"
           href="https://wa.me/51900286035?text=Hola%2C%20tengo%20una%20consulta%20sobre%20el%20cat%C3%A1logo%20de%20motos"
           target="_blank" rel="noopener">¿Necesitas ayuda? Escríbenos por WhatsApp ↗</a>
        <form class="cart-checkout" id="cart-checkout" data-endpoint="api/pedidos.php" hidden>
            <h3>Datos para el pedido</h3>
            <input type="hidden" name="_csrf"
                   value="<?= Herramientas::escaparHtml($_SESSION['_csrf'] ??= bin2hex(random_bytes(32))) ?>">
            <label>Nombres<input name="nombres" autocomplete="given-name" maxlength="100" required></label>
            <label>Apellidos<input name="apellidos" autocomplete="family-name" maxlength="100" required></label>
            <label>DNI<input name="dni" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required></label>
            <label>Celular<input name="telefono" type="tel" autocomplete="tel" maxlength="20" required></label>
            <label>Correo<input name="correo" type="email" autocomplete="email" maxlength="120" required></label>
            <label>Forma de pago
                <select name="modalidad" id="cart-mode">
                    <option value="Contado">Contado · pagar con Mercado Pago</option>
                    <option value="Crédito">Crédito · ir a solicitud de crédito</option>
                </select>
            </label>
            <div class="cart-credit-fields" data-cart-credit hidden>
                <label>Inicial propuesta (S/)
                    <input name="inicial" type="number" min="0" step="0.01" inputmode="decimal">
                </label>
                <label>Cuotas propuestas
                    <select name="cuotas">
                        <option value="">Selecciona</option>
                        <option>6</option>
                        <option>12</option>
                        <option>18</option>
                        <option>24</option>
                        <option>36</option>
                    </select>
                </label>
                <small>La aprobación y las condiciones del crédito se validan con Norcentro.</small>
            </div>
            <p class="cart-feedback" data-cart-feedback role="status"></p>
            <button type="submit" class="button primary" data-cart-submit>Continuar a Mercado Pago</button>
            <small class="cart-privacy-note">
                El pago con tarjeta se completa en el entorno seguro de Mercado Pago.
                Norcentro nunca recibe los datos de tu tarjeta.
            </small>
        </form>
    </section>
</dialog>
