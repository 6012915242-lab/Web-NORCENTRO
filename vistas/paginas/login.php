<?php
use App\Configuracion\Herramientas;
?>
<main class="auth-wrap">
    <section class="auth-card">
        <span class="eyebrow">Área privada</span>
        <h1>Iniciar sesión</h1>
        <p>Ingresa con tu usuario para gestionar la tienda.</p>

        <form method="post" class="form-stack">
            <?= Herramientas::campoCsrf() ?>
            <input type="hidden" name="action" value="iniciar_sesion">

            <label>
                Usuario
                <input name="usuario" autocomplete="username" required autofocus>
            </label>

            <label>
                Contraseña
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="button primary wide">Ingresar</button>
        </form>
    </section>
</main>
