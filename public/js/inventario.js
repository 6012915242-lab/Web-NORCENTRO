/*
 * Inventario sin recargas (AJAX con Fetch API).
 * La búsqueda y el botón "Guardar" se envían en segundo plano y solo se reemplaza
 * la tabla, así la página no salta al inicio. Si JavaScript falla, los formularios
 * siguen funcionando de la forma tradicional (recargando la página).
 */
(() => {
  const seccionUnidades = document.querySelector("#unidades-inventario");
  const filtro = document.querySelector("[data-filtro-inventario]");
  if (!seccionUnidades || !filtro) {
    return;
  }

  const campoBusqueda = filtro.querySelector('[name="inventario_q"]');
  const campoEstado = filtro.querySelector('[name="estado_inventario"]');

  const mostrarAviso = (tipo, mensaje) => {
    document.querySelectorAll(".toast").forEach((aviso) => aviso.remove());
    const aviso = document.createElement("div");
    aviso.className = `toast ${tipo}`;
    aviso.setAttribute("role", "status");
    aviso.textContent = mensaje;
    document.body.appendChild(aviso);
    setTimeout(() => aviso.classList.add("dismissed"), 4000);
  };

  // Copia en la página actual las partes que cambian: tabla de unidades, stock y total.
  const actualizarDesde = (html) => {
    const documento = new DOMParser().parseFromString(html, "text/html");
    const reemplazar = (selector) => {
      const nuevo = documento.querySelector(selector);
      const actual = document.querySelector(selector);
      if (nuevo && actual) {
        actual.replaceWith(nuevo);
      }
    };
    reemplazar("#unidades-inventario tbody");
    reemplazar("#stock-por-color tbody");
    reemplazar("[data-total-inventario]");

    const aviso = documento.querySelector(".toast");
    if (aviso) {
      mostrarAviso(
        aviso.classList.contains("error") ? "error" : "success",
        aviso.textContent.trim(),
      );
    }
  };

  const urlDelFiltro = () => {
    const parametros = new URLSearchParams({ page: "inventario" });
    if (campoBusqueda.value.trim() !== "") {
      parametros.set("inventario_q", campoBusqueda.value.trim());
    }
    if (campoEstado.value !== "") {
      parametros.set("estado_inventario", campoEstado.value);
    }

    return `index.php?${parametros.toString()}`;
  };

  let ultimaPeticion = 0;
  const buscar = async () => {
    const url = urlDelFiltro();
    const numeroPeticion = ++ultimaPeticion;
    try {
      const respuesta = await fetch(url, { credentials: "same-origin" });
      // Si llegó una respuesta más nueva mientras tanto, esta se descarta.
      if (numeroPeticion !== ultimaPeticion) {
        return;
      }
      actualizarDesde(await respuesta.text());
      history.replaceState(null, "", url);
    } catch {
      filtro.submit();
    }
  };

  // Buscar al presionar el botón, al escribir (con una pequeña espera) o al cambiar el estado.
  let temporizador;
  filtro.addEventListener("submit", (evento) => {
    evento.preventDefault();
    buscar();
  });
  campoBusqueda.addEventListener("input", () => {
    clearTimeout(temporizador);
    temporizador = setTimeout(buscar, 300);
  });
  campoEstado.addEventListener("change", buscar);
  filtro.querySelector('a[href*="page=inventario"]')?.addEventListener("click", (evento) => {
    evento.preventDefault();
    campoBusqueda.value = "";
    campoEstado.value = "";
    buscar();
  });

  // Guardar una unidad: se envía el formulario de la fila y se actualiza la tabla.
  seccionUnidades.addEventListener("submit", async (evento) => {
    const formulario = evento.target.closest(".inventory-unit-form");
    if (!formulario) {
      return;
    }
    evento.preventDefault();

    // El filtro pudo cambiar sin recargar: se envía el actual para volver a la misma vista.
    formulario.querySelector('[name="inventario_q"]').value = campoBusqueda.value.trim();
    formulario.querySelector('[name="estado_inventario"]').value = campoEstado.value;

    const idFila = formulario.closest("tr")?.id;
    const boton = formulario.querySelector("button");
    boton.disabled = true;
    try {
      const respuesta = await fetch(formulario.action || "index.php?page=inventario", {
        method: "POST",
        body: new FormData(formulario),
        credentials: "same-origin",
      });
      actualizarDesde(await respuesta.text());
      const fila = idFila ? document.getElementById(idFila) : null;
      fila?.classList.add("fila-guardada");
    } catch {
      formulario.submit();
    } finally {
      boton.disabled = false;
    }
  });
})();
