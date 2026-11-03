// Mantiene el carrito del catálogo y dirige al flujo de contado o de crédito.
(() => {
  const dialog = document.querySelector("#cart-dialog");
  if (!dialog) {
    document.querySelectorAll(".buy-on-credit").forEach((button) =>
      button.addEventListener("click", () => {
        if (button.disabled) return;
        const key = "norcentro.cart.v1";
        let cart = [];
        try {
          const value = JSON.parse(localStorage.getItem(key) || "[]");
          if (Array.isArray(value)) cart = value;
        } catch {}
        const id = Number(button.dataset.id);
        const colorId = Number(button.dataset.colorId || 0);
        const item = cart.find(
          (entry) => Number(entry.id) === id && Number(entry.colorId || 0) === colorId,
        );
        if (item) item.quantity = Math.min(20, Number(item.quantity) + 1);
        else
          cart.push({
            id,
            colorId,
            color: button.dataset.color || "",
            name: button.dataset.name || "",
            price: Number(button.dataset.price || 0),
            image: button.dataset.image || "",
            quantity: 1,
          });
        localStorage.setItem(key, JSON.stringify(cart));
        window.location.assign("index.php?page=compra_credito");
      }),
    );
    return;
  }

  const storageKey = "norcentro.cart.v1";
  const lines = dialog.querySelector("[data-cart-lines]");
  const empty = dialog.querySelector("[data-cart-empty]");
  const total = dialog.querySelector("[data-cart-total]");
  const count = document.querySelector("[data-cart-count]");
  const checkout = document.querySelector("#cart-checkout");
  const startCheckout = dialog.querySelector("[data-cart-start]");
  const mode = document.querySelector("#cart-mode");
  const creditFields = dialog.querySelector("[data-cart-credit]");
  const feedback = dialog.querySelector("[data-cart-feedback]");
  const submit = dialog.querySelector("[data-cart-submit]");
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
      return Array.isArray(value) ? value : [];
    } catch {
      return [];
    }
  };
  let cart = readCart();
  const save = () => localStorage.setItem(storageKey, JSON.stringify(cart));
  const render = () => {
    cart = cart.filter(
      (item) =>
        Number.isInteger(Number(item.id)) && Number(item.id) > 0 && Number(item.quantity) > 0,
    );
    save();
    const units = cart.reduce((sum, item) => sum + Number(item.quantity), 0);
    count.textContent = String(units);
    count.setAttribute("aria-label", `${units} productos en el carrito`);
    empty.hidden = cart.length > 0;
    lines.hidden = cart.length === 0;
    startCheckout.disabled = cart.length === 0;
    if (!cart.length) checkout.hidden = true;
    lines.innerHTML = cart
      .map(
        (item) =>
          `<article class="cart-line" data-line-key="${escape(item.id)}:${escape(item.colorId)}"><img src="${escape(item.image)}" alt=""><div class="cart-line-copy"><strong>${escape(item.name)}</strong><small>${escape(item.color || "Color por confirmar")}</small><b>${currency(item.price)}</b></div><div class="cart-line-controls"><button type="button" data-cart-minus aria-label="Quitar una unidad">−</button><span>${Number(item.quantity)}</span><button type="button" data-cart-plus aria-label="Agregar una unidad">+</button><button type="button" data-cart-remove class="cart-remove">Quitar</button></div></article>`,
      )
      .join("");
    total.textContent = currency(
      cart.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0),
    );
  };
  const setFeedback = (message, kind = "") => {
    feedback.textContent = message;
    feedback.className = `cart-feedback ${kind}`.trim();
  };

  const addItem = (button) => {
    if (button.disabled) return;
    const id = Number(button.dataset.id);
    const colorId = Number(button.dataset.colorId || 0);
    const existing = cart.find(
      (item) => Number(item.id) === id && Number(item.colorId || 0) === colorId,
    );
    if (existing) existing.quantity = Math.min(20, Number(existing.quantity) + 1);
    else
      cart.push({
        id,
        colorId,
        color: button.dataset.color || "",
        name: button.dataset.name || "",
        price: Number(button.dataset.price || 0),
        image: button.dataset.image || "",
        quantity: 1,
      });
    checkout.dataset.requestId = "";
    render();
    const oldLabel = button.textContent;
    button.textContent = "Agregada al carrito";
    window.setTimeout(() => {
      button.textContent = oldLabel;
    }, 1200);
  };
  document
    .querySelectorAll(".add-to-cart")
    .forEach((button) => button.addEventListener("click", () => addItem(button)));
  document.querySelectorAll(".buy-on-credit").forEach((button) =>
    button.addEventListener("click", () => {
      addItem(button);
      window.location.assign("index.php?page=compra_credito");
    }),
  );

  document.querySelector("#cart-toggle")?.addEventListener("click", () => dialog.showModal());
  document.querySelector("#cart-close")?.addEventListener("click", () => dialog.close());
  dialog.addEventListener("click", (event) => {
    if (event.target === dialog) dialog.close();
  });
  lines.addEventListener("click", (event) => {
    const line = event.target.closest("[data-line-key]");
    if (!line) return;
    const [id, colorId] = line.dataset.lineKey.split(":").map(Number);
    const item = cart.find(
      (entry) => Number(entry.id) === id && Number(entry.colorId || 0) === colorId,
    );
    if (!item) return;
    if (event.target.closest("[data-cart-plus]"))
      item.quantity = Math.min(20, Number(item.quantity) + 1);
    else if (event.target.closest("[data-cart-minus]")) item.quantity -= 1;
    else if (event.target.closest("[data-cart-remove]")) item.quantity = 0;
    checkout.dataset.requestId = "";
    cart = cart.filter((entry) => entry.quantity > 0);
    render();
  });
  startCheckout.addEventListener("click", () => {
    checkout.hidden = false;
    checkout.querySelector('[name="nombres"]').focus();
    checkout.scrollIntoView({ behavior: "smooth", block: "start" });
  });
  const refreshMode = () => {
    creditFields.hidden = true;
    creditFields.querySelector('[name="cuotas"]').required = false;
    submit.textContent = "Continuar a Mercado Pago";
  };
  mode.addEventListener("change", () => {
    if (mode.value === "Crédito") {
      const formData = new FormData(checkout);
      const customer = Object.fromEntries(
        ["nombres", "apellidos", "dni", "telefono", "correo"].map((key) => [
          key,
          formData.get(key) || "",
        ]),
      );
      customer.inicial = formData.get("inicial") || "";
      customer.cuotas = formData.get("cuotas") || "";
      sessionStorage.setItem("norcentro.credit-customer.v1", JSON.stringify(customer));
      window.location.assign("index.php?page=compra_credito");
      return;
    }
    refreshMode();
  });
  refreshMode();
  checkout.addEventListener("input", () => {
    checkout.dataset.requestId = "";
  });
  checkout.addEventListener("change", () => {
    checkout.dataset.requestId = "";
  });

  checkout.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!cart.length || !checkout.reportValidity()) return;
    const formData = new FormData(checkout);
    const payload = {
      request_id: checkout.dataset.requestId || (checkout.dataset.requestId = crypto.randomUUID()),
      csrf: formData.get("_csrf"),
      items: cart.map(({ id, colorId, quantity }) => ({
        moto_id: id,
        color_id: colorId || null,
        cantidad: quantity,
      })),
      cliente: {
        nombres: formData.get("nombres"),
        apellidos: formData.get("apellidos"),
        dni: formData.get("dni"),
        telefono: formData.get("telefono"),
        correo: formData.get("correo"),
      },
      pago: {
        modalidad: formData.get("modalidad"),
        inicial: formData.get("inicial") || 0,
        cuotas: formData.get("cuotas") || 0,
      },
    };
    submit.disabled = true;
    submit.textContent = "Procesando pedido…";
    setFeedback("Validando disponibilidad y precios actuales…");
    try {
      const response = await fetch(checkout.dataset.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": String(payload.csrf) },
        body: JSON.stringify(payload),
        credentials: "same-origin",
      });
      const result = await response.json();
      if (!response.ok || !result.ok)
        throw new Error(result.message || "No se pudo completar el pedido.");
      if (result.checkout_url) {
        localStorage.removeItem(storageKey);
        window.location.assign(result.checkout_url);
        return;
      }
      setFeedback(result.message || `Pedido #${result.pedido_id} creado.`, "success");
    } catch (error) {
      setFeedback(error.message || "No se pudo conectar con la tienda. Intenta de nuevo.", "error");
    } finally {
      submit.disabled = false;
      refreshMode();
    }
  });

  render();
})();
