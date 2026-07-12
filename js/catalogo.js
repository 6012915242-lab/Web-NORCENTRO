// ===============================
// CATÁLOGO DINÁMICO NORCENTRO
// ===============================

// Estado global
let motosFiltradas = [...motos];

// ===============================
// RENDER PRINCIPAL (MAP)
// ===============================
function renderMotos(lista) {
    const contenedor = document.getElementById("contenedorMotos");

    contenedor.innerHTML = lista.map(m => `
        <div class="col-12 col-sm-6 col-lg-4">
            <article class="card-moto">
                <div class="card-media">
                    <img src="${m.imagen}" style="width:100%; height:220px; object-fit:cover;">
                </div>

                <div class="card-body">
                    <h3>${m.nombre}</h3>
                    <p>${m.marca} · ${m.tipo}</p>
                    <p class="price">S/. ${m.precio}</p>

                    <a href="detalle.html?id=${m.id}" class="btn-dark">
                        Ver más
                    </a>
                </div>
            </article>
        </div>
    `).join("");
}

// ===============================
// FILTROS
// ===============================

function aplicarFiltros() {

    const precioMax = Number(document.getElementById("filtroPrecio").value || 40000);

    const marcasSeleccionadas = [...document.querySelectorAll(".filtros .marca-group input[type='checkbox']")]
        .filter(c => c.checked)
        .map(c => c.value.toLowerCase());

    const tiposSeleccionados = [...document.querySelectorAll(".filtros .tipo-group input[type='checkbox']")]
        .filter(c => c.checked)
        .map(c => c.value.toLowerCase());

    motosFiltradas = motos.filter(m => {

        const cumplePrecio = Number(m.precio) <= precioMax;

        const cumpleMarca =
            marcasSeleccionadas.length === 0 ||
            marcasSeleccionadas.includes(m.marca.toLowerCase());

        const cumpleTipo =
            tiposSeleccionados.length === 0 ||
            tiposSeleccionados.includes(m.tipo.toLowerCase());

        return cumplePrecio && cumpleMarca && cumpleTipo;
    });

    renderMotos(motosFiltradas);
}

// ===============================
// EVENTOS
// ===============================

// slider precio
document.getElementById("filtroPrecio").addEventListener("input", aplicarFiltros);

// checkboxes
document.querySelectorAll(".filtros .marca-group input[type='checkbox']")
    .forEach(cb => cb.addEventListener("change", aplicarFiltros));

document.querySelectorAll(".filtros .tipo-group input[type='checkbox']")
    .forEach(cb => cb.addEventListener("change", aplicarFiltros));

// ===============================
// INICIO
// ===============================
renderMotos(motos);
const filtroPrecio = document.getElementById("filtroPrecio");
const valorPrecio = document.getElementById("valorPrecio");

filtroPrecio.addEventListener("input", function(){

    let precio = Number(this.value);

    valorPrecio.textContent = 
        "$" + precio.toLocaleString();

});