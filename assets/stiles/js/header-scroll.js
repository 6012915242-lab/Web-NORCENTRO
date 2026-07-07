/**
 * header-scroll.js
 * ------------------------------------------------------------
 * Controla el comportamiento del header en páginas con .hero
 * (por ahora solo index.html):
 *
 *  - Al cargar: el header se muestra TRANSPARENTE, flotando
 *    sobre la imagen del hero (el CSS ya se encarga de esto
 *    vía "body:has(.hero) .site-header").
 *  - Al hacer scroll: se le agrega la clase "scrolled", que
 *    en el CSS cambia el fondo a sólido para mantener la
 *    legibilidad del menú.
 *
 * Si la página no tiene .hero, el script no hace nada
 * (el header se comporta como siempre: sólido y sticky).
 * ------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const hero = document.querySelector('.hero');

  // Si no hay header o no hay hero en esta página, no aplicamos nada.
  if (!header || !hero) return;

  // Umbral de scroll (en px) a partir del cual el header se vuelve sólido.
  const SCROLL_THRESHOLD = 60;

  const updateHeaderState = () => {
    const shouldBeSolid = window.scrollY > SCROLL_THRESHOLD;
    header.classList.toggle('scrolled', shouldBeSolid);
  };

  // Estado inicial (por si la página carga ya scrolleada, ej. al recargar)
  updateHeaderState();

  // Escuchamos el scroll de forma eficiente (passive = no bloquea el render)
  window.addEventListener('scroll', updateHeaderState, { passive: true });

  // Por si el usuario redimensiona la ventana y cambia el layout
  window.addEventListener('resize', updateHeaderState);
});