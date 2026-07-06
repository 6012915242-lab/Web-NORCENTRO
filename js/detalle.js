const params = new URLSearchParams(window.location.search);
const id = parseInt(params.get("id"));

const motoEncontrada = motos.find(m => m.id === id);

const contenedor = document.getElementById("detalleMoto");

if (motoEncontrada) {

    contenedor.innerHTML = `
        <h1>${motoEncontrada.nombre}</h1>

        <img src="${motoEncontrada.imagen}" style="width:400px; max-width:100%;">

        <p><strong>Marca:</strong> ${motoEncontrada.marca}</p>
        <p><strong>Tipo:</strong> ${motoEncontrada.tipo}</p>
        <p><strong>Precio:</strong> S/. ${motoEncontrada.precio}</p>

        <button class="btn-dark" onclick="agregarCarrito(${motoEncontrada.id})">
            Agregar al carrito
        </button>
    `;

} else {
    contenedor.innerHTML = `<h2>Moto no encontrada</h2>`;
}

function agregarCarrito(id) {

    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

    const moto = motos.find(m => m.id === id);

    if (!moto) return;

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

    alert("Agregado al carrito ✔");
}