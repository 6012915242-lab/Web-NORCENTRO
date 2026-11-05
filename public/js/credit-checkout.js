// Envía la solicitud de compra a crédito al servidor y presenta su resultado.
(() => {
  const form = document.querySelector("#credit-checkout");
  if (!form) return;

  const storageKey = "norcentro.cart.v1";
  const customerKey = "norcentro.credit-customer.v1";
  const lines = document.querySelector("[data-credit-lines]");
  const empty = document.querySelector("[data-credit-empty]");
  const total = document.querySelector("[data-credit-total]");
  const feedback = document.querySelector("[data-credit-feedback]");
  const success = document.querySelector("[data-credit-success]");
  const successId = document.querySelector("[data-credit-success-id]");
  const summary = document.querySelector(".credit-purchase-summary");
  const layout = document.querySelector(".credit-purchase-layout");
  const submit = document.querySelector("[data-credit-submit]");
  const currency = (value) =>
    `S/ ${Number(value || 0).toLocaleString("es-PE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const escape = (value) =>
    String(value ?? "").replace(
      /[&<>"']/g,
      (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[char],
    );
  const readCart = () => {
    try {
      const value = JSON.parse(localStorage.getItem(storageKey) || "[]");
      return Array.isArray(value)
        ? value.filter((item) => Number(item.id) > 0 && Number(item.quantity) > 0)
        : [];
    } catch {
      return [];
    }
  };
  let cart = readCart();
  const setFeedback = (message, kind = "") => {
    feedback.textContent = message;
    feedback.className = `credit-form-feedback ${kind}`.trim();
  };
  const renderCart = () => {
    empty.hidden = cart.length > 0;
    lines.hidden = cart.length === 0;
    submit.disabled = cart.length === 0;
    lines.innerHTML = cart
      .map(
        (item) =>
          `<article class="credit-cart-line">
            <img src="${escape(item.image)}" alt="">
            <div>
              <strong>${escape(item.name)}</strong>
              <small>${escape(item.color || "Color por confirmar")} · ${Number(item.quantity)} unidad(es)</small>
            </div>
            <b>${currency(Number(item.price) * Number(item.quantity))}</b>
          </article>`,
      )
      .join("");
    total.textContent = currency(
      cart.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0),
    );
  };

  try {
    const customer = JSON.parse(sessionStorage.getItem(customerKey) || "{}");
    for (const key of ["nombres", "apellidos", "dni", "telefono", "correo", "inicial", "cuotas"]) {
      if (customer[key]) form.elements[key].value = customer[key];
    }
  } catch {
    sessionStorage.removeItem(customerKey);
  }

  form.addEventListener("input", () => {
    form.dataset.requestId = "";
  });
  form.addEventListener("change", () => {
    form.dataset.requestId = "";
  });
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!cart.length || !form.reportValidity()) return;
    const values = new FormData(form);
    const requestId = form.dataset.requestId || (form.dataset.requestId = crypto.randomUUID());
    const payload = {
      request_id: requestId,
      csrf: values.get("_csrf"),
      items: cart.map(({ id, colorId, quantity }) => ({
        moto_id: id,
        color_id: colorId || null,
        cantidad: quantity,
      })),
      cliente: {
        nombres: values.get("nombres"),
        apellidos: values.get("apellidos"),
        dni: values.get("dni"),
        telefono: values.get("telefono"),
        correo: values.get("correo"),
      },
      pago: {
        modalidad: "Crédito",
        inicial: values.get("inicial") || 0,
        cuotas: values.get("cuotas") || 0,
      },
      acepta_credito: values.get("acepta_credito") === "1",
    };
    submit.disabled = true;
    submit.textContent = "Enviando solicitud…";
    setFeedback("Verificando el pedido y la disponibilidad…");
    try {
      const response = await fetch(form.dataset.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": String(payload.csrf) },
        body: JSON.stringify(payload),
        credentials: "same-origin",
      });
      const result = await response.json();
      if (!response.ok || !result.ok)
        throw new Error(result.message || "No se pudo enviar la solicitud.");
      localStorage.removeItem(storageKey);
      sessionStorage.removeItem(customerKey);
      cart = [];
      renderCart();
      form.hidden = true;
      if (summary) summary.hidden = true;
      layout?.classList.add("credit-request-sent");
      const proposedInitial = Number(values.get("inicial") || 0);
      const proposedTerms = Number(values.get("cuotas") || 0);
      const proposalText = ` · Propuesta: inicial ${proposedInitial ? currency(proposedInitial) : "por coordinar"} · ${proposedTerms ? `${proposedTerms} cuotas` : "plazo por coordinar"}`;
      if (successId)
        successId.textContent = `Solicitud #${result.pedido_id} · Pendiente de evaluación${proposalText}`;
      if (success) success.hidden = false;
    } catch (error) {
      setFeedback(
        error.message || "No se pudo conectar con Norcentro. Intenta nuevamente.",
        "error",
      );
      submit.disabled = false;
      submit.textContent = "Enviar solicitud de crédito";
    }
  });

  renderCart();
})();
