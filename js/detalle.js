// ===============================
// DETALLE DINÁMICO
// ===============================

// obtener ID de la URL
const params = new URLSearchParams(window.location.search);
const id = parseInt(params.get("id"));

// buscar moto
const moto = motos.find(m => m.id === id);

// contenedor
const contenedor = document.getElementById("detalleMoto");

// render
if (moto) {
    contenedor.innerHTML = `
        <h1>${moto.nombre}</h1>

        <img src="${moto.imagen}" style="width:400px; max-width:100%;">

        <p><strong>Marca:</strong> ${moto.marca}</p>
        <p><strong>Tipo:</strong> ${moto.tipo}</p>
        <p><strong>Precio:</strong> S/. ${moto.precio}</p>

       <button class="btn-dark" onclick="agregarCarrito(${moto.id})">
    Agregar al carrito
</button>
    `;
} else {
    contenedor.innerHTML = `<h2>Moto no encontrada</h2>`;
}
function agregarCarrito(id) {

    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

    const moto = motos.find(m => m.id === id);

    const existe = carrito.find(item => item.id === id);

    if (existe) {
        existe.cantidad += 1;
    } else {
        carrito.push({
            id: moto.id,
            nombre: moto.nombre,
            precio: moto.precio,
            imagen: moto.imagen,
            cantidad: 1
        });
    }

    localStorage.setItem("carrito", JSON.stringify(carrito));

    alert("Agregado al carrito");
}