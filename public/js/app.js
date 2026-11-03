// Actualiza la selección de venta, la cotización y los controles del panel.
(() => {
  const unit = document.querySelector("#unit-select");
  const preview = document.querySelector("#selected-moto");
  const mode = document.querySelector("#sale-mode");
  const creditFields = document.querySelector("#credit-fields");
  const quote = document.querySelector("#sale-quote");
  const clientSelect = document.querySelector("#sale-client-select");
  const discountInput = document.querySelector("#sale-discount");
  const initialInput = document.querySelector("#sale-initial");
  const installmentInput = document.querySelector("#sale-installments");
  const dueInput = document.querySelector("#sale-first-due");
  const formatMoney = (value) =>
    `S/ ${Number(value || 0).toLocaleString("es-PE", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  document.querySelectorAll("[data-fill-payment]").forEach((button) =>
    button.addEventListener("click", () => {
      const amount = button.closest(".pay-form")?.querySelector('input[name="monto"]');
      if (!amount) return;
      amount.value = amount.max;
      amount.focus();
    }),
  );
  const refreshMoto = () => {
    if (!unit || !preview) return;
    const option = unit.selectedOptions[0];
    if (!option || !option.value) {
      preview.classList.remove("visible");
      preview.querySelector("span").textContent =
        "Selecciona una unidad para ver su foto y precio.";
      return;
    }
    const img = preview.querySelector("img");
    img.src = option.dataset.image;
    img.alt = option.dataset.model;
    preview.querySelector("span").innerHTML =
      `<b>${escapeHtml(option.dataset.model)}</b><small>Precio de lista: S/ ${Number(option.dataset.price).toLocaleString("es-PE", { minimumFractionDigits: 2 })} · ${escapeHtml(option.textContent.split("·")[1]?.trim() || "")}</small>`;
    preview.classList.add("visible");
  };
  const escapeHtml = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c],
    );
  const refreshMode = () => {
    if (creditFields && mode) creditFields.classList.toggle("visible", mode.value === "Crédito");
  };
  const refreshQuote = () => {
    if (!quote) return;
    const selectedClient = clientSelect?.selectedOptions[0];
    const selectedUnit = unit?.selectedOptions[0];
    const hasUnit = Boolean(selectedUnit?.value);
    const price = hasUnit ? Number(selectedUnit.dataset.price || 0) : 0;
    const discount = Math.max(0, Number(discountInput?.value || 0));
    const total = Math.max(0, price - discount);
    const isCredit = mode?.value === "Crédito";
    const initial = Math.max(0, Number(initialInput?.value || 0));
    const capitalCents = Math.max(0, Math.round(total * 100) - Math.round(initial * 100));
    const surchargeRate = Number(quote.dataset.porcentajeRecargo || 0);
    const surchargeCents = Math.floor((capitalCents * surchargeRate + 50) / 100);
    const financedCents = capitalCents + surchargeCents;
    const installments = Math.max(1, Math.floor(Number(installmentInput?.value || 1)));
    const baseCents = Math.floor(financedCents / installments);
    const remainderCents = financedCents % installments;
    const firstInstallment = (baseCents + (remainderCents > 0 ? 1 : 0)) / 100;
    const lastInstallment = baseCents / 100;
    const clientName = selectedClient?.value ? selectedClient.dataset.name : "";
    const clientDni = selectedClient?.value ? selectedClient.dataset.dni : "";
    const put = (id, value) => {
      const element = document.querySelector(id);
      if (element) element.textContent = value;
    };
    put(
      "#quote-client",
      clientName ? `${clientName} · DNI ${clientDni}` : "Pendiente de seleccionar",
    );
    put(
      "#quote-unit",
      hasUnit
        ? `${selectedUnit.dataset.model} · Serie ${selectedUnit.dataset.series}`
        : "Pendiente de seleccionar",
    );
    put("#quote-price", formatMoney(price));
    put("#quote-discount", `− ${formatMoney(discount)}`);
    put("#quote-total", formatMoney(total));
    put("#quote-mode", isCredit ? "Crédito" : "Contado");
    document.querySelectorAll(".quote-credit-line").forEach((line) => {
      line.hidden = !isCredit;
    });
    put("#quote-initial", formatMoney(initial));
    put("#quote-capital", formatMoney(capitalCents / 100));
    put("#quote-surcharge", formatMoney(surchargeCents / 100));
    put("#quote-financed", formatMoney(financedCents / 100));
    put("#quote-credit-total", formatMoney((Math.round(initial * 100) + financedCents) / 100));
    put(
      "#quote-installment",
      baseCents > 0
        ? `${installments} cuotas de ${formatMoney(firstInstallment)} aprox.; última ${formatMoney(lastInstallment)}`
        : "Ingresa una inicial menor al total.",
    );
    const due = dueInput?.value || "";
    put("#quote-due", due ? due.split("-").reverse().join("/") : "Selecciona una fecha");
    put(
      "#quote-footnote",
      isCredit
        ? "Recargo único del 5 % sobre el saldo después de la inicial. Algunas cuotas pueden variar un centavo por redondeo."
        : "Cotización referencial. El total final se confirma al registrar la venta y validar nuevamente la disponibilidad.",
    );
  };
  unit?.addEventListener("change", refreshMoto);
  mode?.addEventListener("change", () => {
    refreshMode();
    refreshQuote();
  });
  clientSelect?.addEventListener("change", () => {
    const clientId = clientSelect.value;
    ["#sale-client-current", "#sale-bike-client"].forEach((selector) => {
      const field = document.querySelector(selector);
      if (field) field.value = clientId;
    });
    refreshQuote();
  });
  [discountInput, initialInput, installmentInput, dueInput].forEach((input) =>
    input?.addEventListener("input", refreshQuote),
  );
  unit?.addEventListener("change", refreshQuote);
  refreshMoto();
  refreshMode();
  refreshQuote();
  document
    .querySelectorAll(".toast")
    .forEach((t) => setTimeout(() => t.classList.add("dismissed"), 5000));
  const homePromo = document.querySelector("#home-promo-dialog");
  if (homePromo && typeof homePromo.showModal === "function") {
    const seenKey = `norcentro-home-promo:${homePromo.dataset.promoId || "current"}`;
    let seen = false;
    try {
      seen = sessionStorage.getItem(seenKey) === "1";
    } catch (_) {}
    const rememberDismissal = () => {
      try {
        sessionStorage.setItem(seenKey, "1");
      } catch (_) {}
    };
    homePromo
      .querySelectorAll("[data-home-promo-dismiss]")
      .forEach((button) => button.addEventListener("click", () => homePromo.close()));
    homePromo.addEventListener("click", (event) => {
      if (event.target === homePromo) homePromo.close();
    });
    homePromo.addEventListener("close", rememberDismissal, { once: true });
    if (!seen)
      window.setTimeout(() => {
        if (!homePromo.open) homePromo.showModal();
      }, 180);
  }
  const mainPhoto = document.querySelector("#product-main-image");
  const galleryButtons = [...document.querySelectorAll(".detail-thumb")];
  const galleryCounter = document.querySelector("[data-gallery-counter]");
  const galleryCaption = document.querySelector("[data-gallery-caption]");
  const galleryPrev = document.querySelector("[data-gallery-prev]");
  const galleryNext = document.querySelector("[data-gallery-next]");
  const visibleGalleryButtons = () =>
    galleryButtons.filter((button) => button.isConnected && !button.hidden);
  const updateGalleryControls = () => {
    const visible = visibleGalleryButtons();
    const index = visible.findIndex((button) => button.classList.contains("active"));
    if (galleryCounter)
      galleryCounter.textContent = `${index < 0 ? 0 : index + 1} / ${visible.length}`;
    if (galleryPrev) galleryPrev.hidden = visible.length < 2;
    if (galleryNext) galleryNext.hidden = visible.length < 2;
  };
  const setMainPhoto = (button) => {
    if (!mainPhoto) return;
    mainPhoto.src = button.dataset.gallerySrc;
    mainPhoto.alt = button.dataset.galleryAlt || "";
    if (galleryCaption) galleryCaption.textContent = button.dataset.galleryLabel || button.dataset.galleryAlt || "Vista de la motocicleta";
    const colorGroup = button.dataset.colorGroup;
    if (colorGroup) {
      const swatch = [...document.querySelectorAll(".moto-color-swatch")].find(
        (item) => item.dataset.colorVariant === colorGroup,
      );
      if (swatch) {
        document.querySelectorAll(".moto-color-swatch").forEach((item) => {
          const active = item === swatch;
          item.classList.toggle("active", active);
          if (active) item.setAttribute("aria-current", "true");
          else item.removeAttribute("aria-current");
        });
        const label = document.querySelector("#selected-color-name");
        if (label) label.textContent = swatch.title;
        const detailColor = document.querySelector("#selected-color-detail");
        if (detailColor) detailColor.textContent = swatch.title;
        document
          .querySelectorAll(
            ".detail-purchase-actions .add-to-cart, .detail-purchase-actions .buy-on-credit",
          )
          .forEach((action) => {
            action.dataset.color = swatch.title;
            action.dataset.colorId = colorGroup;
            action.dataset.image = button.dataset.gallerySrc;
          });
      }
    }
    galleryButtons.forEach((item) => {
      const active = item === button;
      item.classList.toggle("active", active);
      item.setAttribute("aria-pressed", active ? "true" : "false");
    });
    updateGalleryControls();
  };
  const missingPhotoUrl = new URL("img/motos/foto-no-disponible.svg", document.baseURI).href;
  const useMissingPhoto = (image) => {
    if (!image || image.dataset.fallbackApplied === "1") return;
    image.dataset.fallbackApplied = "1";
    image.classList.add("image-fallback");
    image.src = missingPhotoUrl;
  };
  // A failed angle disappears from the gallery and its counter. Other broken
  // product images get an explicit neutral placeholder instead of an empty box
  // or a different motorcycle/color.
  document.addEventListener(
    "error",
    (event) => {
      const image = event.target;
      if (!(image instanceof HTMLImageElement) || image.dataset.fallbackApplied === "1") return;
      const failedUrl = image.currentSrc || image.src;
      const thumb = image.closest(".detail-thumb");
      if (thumb) {
        const wasActive = thumb.classList.contains("active");
        thumb.hidden = true;
        thumb.classList.remove("active");
        const visible = visibleGalleryButtons();
        if (wasActive) {
          const next = visible[0];
          if (next) setMainPhoto(next);
          else if (mainPhoto) useMissingPhoto(mainPhoto);
        }
        updateGalleryControls();
        return;
      }
      if (image === mainPhoto) {
        const failedThumb = visibleGalleryButtons().find(
          (button) => button.dataset.gallerySrc === failedUrl,
        );
        if (failedThumb) {
          failedThumb.hidden = true;
          failedThumb.classList.remove("active");
        }
        const next = visibleGalleryButtons()[0];
        if (next) {
          setMainPhoto(next);
          return;
        }
        updateGalleryControls();
      }
      useMissingPhoto(image);
    },
    true,
  );
  // Images may have failed before this script was evaluated.
  document.querySelectorAll("img").forEach((image) => {
    if (image.complete && image.naturalWidth === 0 && image.src !== missingPhotoUrl) {
      image.dispatchEvent(new Event("error"));
    }
  });
  galleryButtons.forEach((button) => button.addEventListener("click", () => setMainPhoto(button)));
  const stepPhoto = (delta) => {
    const visible = visibleGalleryButtons();
    if (visible.length < 2) return;
    let index = visible.findIndex((button) => button.classList.contains("active"));
    index = (index + delta + visible.length) % visible.length;
    setMainPhoto(visible[index]);
  };
  galleryPrev?.addEventListener("click", () => stepPhoto(-1));
  galleryNext?.addEventListener("click", () => stepPhoto(1));
  mainPhoto?.addEventListener("keydown", (event) => {
    if (event.key === "ArrowLeft") stepPhoto(-1);
    if (event.key === "ArrowRight") stepPhoto(1);
  });
  // Each color swatch is a link to a server-rendered gallery for that exact
  // finish. Keeping one color's photos in the DOM prevents mixed thumbnails.
  const contactForm = document.querySelector("#contact-form");
  contactForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(contactForm);
    const message = [
      "Hola, escribo desde la página de NORCENTRO.",
      `Nombre: ${data.get("nombre")}`,
      `Correo: ${data.get("correo")}`,
      `Teléfono: ${data.get("telefono") || "No indicado"}`,
      `Asunto: ${data.get("asunto")}`,
      `Mensaje: ${data.get("mensaje")}`,
    ].join("\n");
    window.open(
      `https://wa.me/51900286035?text=${encodeURIComponent(message)}`,
      "_blank",
      "noopener",
    );
  });
})();
