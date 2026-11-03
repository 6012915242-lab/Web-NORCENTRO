// Consulta el estado del pedido después de iniciar el pago en línea.
(() => {
  const page = document.querySelector(".checkout-result[data-request-id]");
  if (!page || !page.dataset.requestId || page.dataset.mode !== "Contado") return;
  const message = page.querySelector("[data-order-message]");
  const badge = page.querySelector("[data-order-status]");
  const textByState = {
    Pagado: "Pago aprobado. Tu compra quedó confirmada.",
    "Pendiente de pago":
      "El pago sigue pendiente en Mercado Pago. Puedes actualizar esta página en unos momentos.",
    Cancelado: "El pedido se canceló o venció. Puedes volver al catálogo e intentarlo otra vez.",
    "Error de pago": "No se pudo iniciar el pago. Vuelve al catálogo y crea un nuevo pedido.",
  };
  const refresh = async () => {
    try {
      const response = await fetch(
        `api/pedidos.php?request_id=${encodeURIComponent(page.dataset.requestId)}`,
        { headers: { Accept: "application/json" }, credentials: "same-origin", cache: "no-store" },
      );
      const data = await response.json();
      if (!response.ok || !data.ok) return;
      badge.textContent = data.estado;
      badge.className = `pill ${data.estado === "Pagado" ? "green" : data.estado === "Cancelado" ? "gray" : "amber"}`;
      message.textContent =
        textByState[data.estado] || "Estamos verificando el resultado del pago con Mercado Pago.";
    } catch {
      /* se conserva el estado inicial y se puede actualizar la página */
    }
  };
  refresh();
  window.setTimeout(refresh, 4500);
})();
