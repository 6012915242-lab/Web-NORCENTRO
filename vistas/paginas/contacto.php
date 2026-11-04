<?php
use App\Configuracion\Herramientas;
?>
<main class="container public-page contact-page">
  <div class="crumb">
    <a href="index.php?page=inicio">NORCENTRO</a>
    <span>/</span> Contacto
  </div>
  <header class="contact-heading">
    <span class="eyebrow">ESTAMOS PARA ATENDERTE</span>
    <h1>Contacto</h1>
    <p>¿Tienes dudas sobre una moto? Escríbenos y te respondemos lo antes posible.</p>
  </header>
  <div class="contact-layout">
    <form class="panel contact-form" id="contact-form">
      <label>
        Nombre completo
        <input name="nombre" autocomplete="name" placeholder="Tu nombre" required maxlength="100">
      </label>
      <label>
        Correo electrónico
        <input name="correo" type="email" autocomplete="email" placeholder="tucorreo@email.com"
               required maxlength="120">
      </label>
      <label>
        Teléfono
        <input name="telefono" type="tel" autocomplete="tel" placeholder="Ej.: 999 999 999" maxlength="20">
      </label>
      <label>
        Asunto
        <select name="asunto" required>
          <option value="">Selecciona un asunto</option>
          <option>Consulta general</option>
          <option>Cotización de moto</option>
          <option>Servicio postventa</option>
          <option>Otro</option>
        </select>
      </label>
      <label>
        Mensaje
        <textarea name="mensaje" rows="5" placeholder="Cuéntanos cómo podemos ayudarte"
                  required maxlength="1000"></textarea>
      </label>
      <button class="button primary" type="submit">
        Enviar mensaje por WhatsApp <span aria-hidden="true">↗</span>
      </button>
      <p class="contact-form-note">Se abrirá WhatsApp con tu mensaje listo para enviar.</p>
    </form>
    <section class="panel contact-information" aria-labelledby="contact-info-title">
      <span class="eyebrow">BAGUA · AMAZONAS</span>
      <h2 id="contact-info-title">Información de contacto</h2>
      <address>
        <p>
          <span aria-hidden="true">⌖</span>
          <span><strong>Visítanos</strong><br>Jr. Heroes del Cenepa 1499, Bagua, Amazonas</span>
        </p>
        <p>
          <span aria-hidden="true">◉</span>
          <span>
            <strong>WhatsApp y teléfono</strong><br>
            <a href="https://wa.me/51900286035" target="_blank" rel="noopener">+51 900 286 035</a>
          </span>
        </p>
        <p>
          <span aria-hidden="true">✉</span>
          <span><strong>Correo de ventas</strong><br><a href="mailto:ventas@norcentro.com">ventas@norcentro.com</a></span>
        </p>
        <p>
          <span aria-hidden="true">◷</span>
          <span><strong>Horario de atención</strong><br>Lun a Sáb · 9:00 - 19:00</span>
        </p>
      </address>
      <a class="button contact-whatsapp" href="https://wa.me/51900286035" target="_blank" rel="noopener">Escríbenos por WhatsApp</a>
      <div class="contact-map-heading">
        <strong>Cómo llegar</strong>
        <a href="https://www.google.com/maps/search/?api=1&amp;query=Jr.+Heroes+del+Cenepa+1499%2C+Bagua%2C+Amazonas%2C+Peru"
           target="_blank" rel="noopener">Abrir en Google Maps ↗</a>
      </div>
      <iframe class="contact-map" title="Mapa para llegar a NORCENTRO, Jr. Héroes del Cenepa 1499, Bagua"
              src="https://maps.google.com/maps?q=Jr.+Heroes+del+Cenepa+1499%2C+Bagua%2C+Amazonas%2C+Peru&amp;z=16&amp;output=embed"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <div class="contact-social">
        <span>Síguenos</span>
        <a href="https://www.facebook.com/tiendanorcentro" target="_blank" rel="noopener">Facebook</a>
        <a href="https://www.instagram.com/norcentrosac/" target="_blank" rel="noopener">Instagram</a>
        <a href="https://www.tiktok.com/@norcentrosac" target="_blank" rel="noopener">TikTok</a>
      </div>
    </section>
  </div>
</main>
