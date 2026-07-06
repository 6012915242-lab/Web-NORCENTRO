const buscarInput = document.getElementById('buscar-moto');
const btnBuscar = document.getElementById('btn-buscar');
const resultadosBusqueda = document.getElementById('resultadosBusqueda');
const sinResultados = document.getElementById('sin-resultados');
const filtroPrecio = document.getElementById('filtroPrecio');
const rangeVal = document.querySelector('.range-val span:last-child');

function obtenerValoresSeleccionados(selector) {
    return [...document.querySelectorAll(selector)]
        .filter(input => input.checked)
        .map(input => input.value.toLowerCase());
}

function renderResultados(lista) {
    resultadosBusqueda.innerHTML = lista.map(m => `
        <div class="col-12 col-sm-6 col-lg-4">
            <article class="card-moto">
                <div class="card-media">
                    <img src="${m.imagen}" alt="${m.nombre}">
                </div>
                <div class="card-body">
                    <h3>${m.nombre}</h3>
                    <p>${m.marca} · ${m.tipo} · ${m.cilindrada}</p>
                    <p class="price">S/. ${m.precio}</p>
                    <a href="detalle.html?id=${m.id}" class="btn-dark">Ver más</a>
                </div>
            </article>
        </div>
    `).join('');

    sinResultados.style.display = lista.length === 0 ? 'block' : 'none';
}

function buscarYFiltrar() {
    const texto = buscarInput.value.trim().toLowerCase();
    const precioMax = Number(filtroPrecio.value || 40000);
    const marcas = obtenerValoresSeleccionados('.filtros .marca-group input[type="checkbox"]');
    const tipos = obtenerValoresSeleccionados('.filtros .tipo-group input[type="checkbox"]');
    const cilindradas = obtenerValoresSeleccionados('.filtros .cilindra-group input[type="checkbox"]');

    const resultados = motos.filter(m => {
        const cumplePrecio = Number(m.precio) <= precioMax;
        const cumpleMarca = marcas.length === 0 || marcas.includes(m.marca.toLowerCase());
        const cumpleTipo = tipos.length === 0 || tipos.includes(m.tipo.toLowerCase());
        const cumpleCilindrada = cilindradas.length === 0 || cilindradas.includes(m.cilindrada.toLowerCase());
        const cumpleTexto = texto === '' || [m.nombre, m.marca, m.tipo, m.cilindrada].some(field => field.toLowerCase().includes(texto));

        return cumplePrecio && cumpleMarca && cumpleTipo && cumpleCilindrada && cumpleTexto;
    });

    renderResultados(resultados);
}

function actualizarValorPrecio() {
    if (rangeVal) {
        rangeVal.textContent = `$${Number(filtroPrecio.value).toLocaleString('en-US')}`;
    }
}

btnBuscar.addEventListener('click', buscarYFiltrar);
buscarInput.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        buscarYFiltrar();
    }
});

filtroPrecio.addEventListener('input', () => {
    actualizarValorPrecio();
    buscarYFiltrar();
});

document.querySelectorAll('.filtros input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', buscarYFiltrar);
});

actualizarValorPrecio();
buscarYFiltrar();
