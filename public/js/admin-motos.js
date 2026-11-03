document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-moto-gallery-form]");
  const container = form?.querySelector("[data-color-groups]");
  const addButton = form?.querySelector("[data-add-color]");

  if (!form || !container || !addButton) {
    return;
  }

  const bindGroup = (group) => {
    const fileInput = group.querySelector('input[type="file"]');
    const counter = group.querySelector("[data-file-count]");
    const removeButton = group.querySelector("[data-remove-color]");

    fileInput?.addEventListener("change", () => {
      const total = fileInput.files?.length || 0;

      if (total > 10) {
        fileInput.value = "";
        counter.textContent = "Máximo 10 fotografías por color.";
        counter.classList.add("error-text");
        return;
      }

      counter.classList.remove("error-text");
      counter.textContent = total > 0
        ? `${total} fotografía(s) seleccionada(s).`
        : "Selecciona entre 1 y 10 fotografías.";
    });

    removeButton?.addEventListener("click", () => {
      group.remove();
      renumberGroups();
    });
  };

  const renumberGroups = () => {
    const groups = [...container.querySelectorAll("[data-color-group]")];

    groups.forEach((group, index) => {
      const legend = group.querySelector("legend");
      if (legend) {
        legend.textContent = `Color ${index + 1}`;
      }

      group.querySelectorAll("[name]").forEach((field) => {
        field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
      });

      const removeButton = group.querySelector("[data-remove-color]");
      if (removeButton) {
        removeButton.hidden = groups.length === 1;
      }
    });

    addButton.disabled = groups.length >= 6;
  };

  addButton.addEventListener("click", () => {
    const groups = container.querySelectorAll("[data-color-group]");
    if (groups.length >= 6) {
      return;
    }

    const template = groups[0];
    const clone = template.cloneNode(true);

    clone.querySelectorAll("input").forEach((input) => {
      if (input.type === "color") {
        input.value = "#cccccc";
      } else {
        input.value = "";
      }

      if (input.name.includes("[nombre]") || input.type === "file") {
        input.required = true;
      }
    });

    const counter = clone.querySelector("[data-file-count]");
    if (counter) {
      counter.textContent = "Selecciona entre 1 y 10 fotografías.";
      counter.classList.remove("error-text");
    }

    container.appendChild(clone);
    bindGroup(clone);
    renumberGroups();
  });

  container.querySelectorAll("[data-color-group]").forEach(bindGroup);
  renumberGroups();
});
