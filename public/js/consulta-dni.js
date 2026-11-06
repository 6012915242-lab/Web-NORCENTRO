document.addEventListener("DOMContentLoaded", () => {
  const formulario = document.querySelector("[data-formulario-cliente]");
  if (!formulario) {
    return;
  }

  const boton = formulario.querySelector("[data-consultar-dni]");
  const mensaje = formulario.querySelector("[data-mensaje-dni]");
  const campoDni = formulario.querySelector('[name="dni"]');

  boton.addEventListener("click", async () => {
    const dni = campoDni.value.trim();
    if (!/^\d{8}$/.test(dni)) {
      mensaje.textContent = "Escribe un DNI de 8 dígitos para consultarlo.";
      campoDni.focus();
      return;
    }

    boton.disabled = true;
    mensaje.textContent = "Consultando DNI...";
    try {
      const datos = new URLSearchParams({
        action: "consultar_dni",
        dni,
        _csrf: formulario.querySelector('[name="_csrf"]').value,
      });
      const respuesta = await fetch("api/consulta-dni.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: datos,
      });
      const resultado = await respuesta.json();
      if (!respuesta.ok || !resultado.ok) {
        throw new Error(resultado.mensaje || "No se pudo consultar el DNI.");
      }
      if (campoDni.value.trim() !== dni) {
        mensaje.textContent = "El DNI cambió durante la consulta. Vuelve a buscarlo.";
        return;
      }
      formulario.querySelector('[name="nombres"]').value = resultado.datos.nombres;
      formulario.querySelector('[name="apellidos"]').value = resultado.datos.apellidos;
      const direccion = resultado.datos.direccion || "";
      if (direccion) {
        formulario.querySelector('[name="direccion"]').value = direccion;
        mensaje.textContent = "Datos encontrados. Revísalos antes de guardar.";
      } else {
        mensaje.textContent =
          "Datos encontrados. Factiliza no devolvió una dirección; puedes ingresarla manualmente.";
      }
    } catch (error) {
      mensaje.textContent =
        error instanceof SyntaxError
          ? "La sesión pudo haber vencido. Recarga la página e inicia sesión nuevamente."
          : error.message || "No se pudo consultar el DNI. Ingresa los datos manualmente.";
    } finally {
      boton.disabled = false;
    }
  });
});
