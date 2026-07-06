// ===============================
// CARRITO REAL
// ===============================

let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

const contenedor = document.getElementById("carritoContenedor");
const totalSpan = document.getElementById("total");

// ===============================
// RENDER
// ===============================
function renderCarrito() {

    contenedor.innerHTML = "";

    let total = 0;

    carrito.forEach(item => {

        total += item.precio * item.cantidad;

        contenedor.innerHTML += `
            <div style="display:flex; gap:15px; margin-bottom:15px; align-items:center;">
                
                <img src="${item.imagen}" width="80">

                <div>
                    <h4>${item.nombre}</h4>
                    <p>S/. ${item.precio}</p>
                    <p>Cantidad: ${item.cantidad}</p>
                </div>

                <button onclick="eliminarItem(${item.id})">X</button>
            </div>
        `;
    });

    totalSpan.textContent = total;
}

// ===============================
// ELIMINAR ITEM
// ===============================
function eliminarItem(id) {

    carrito = carrito.filter(item => item.id !== id);

    localStorage.setItem("carrito", JSON.stringify(carrito));

    renderCarrito();
}

// inicial
renderCarrito();