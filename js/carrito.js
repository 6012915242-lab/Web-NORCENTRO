// ===============================
// CARRITO REAL FUNCIONAL
// ===============================

let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

const contenedor = document.getElementById("carritoContenedor");
const totalSpan = document.getElementById("total");
const subtotalSpan = document.getElementById("subtotal");
const envioSpan = document.getElementById("envio");
const totalFinalSpan = document.getElementById("totalFinal");
const btnWhatsApp = document.getElementById("btnWhatsApp");

const ENVIO = 50;

// ===============================
// RENDER CARRITO
// ===============================
function renderCarrito() {

    contenedor.innerHTML = "";

    let subtotal = 0;

    carrito.forEach(item => {

        subtotal += item.precio * item.cantidad;

        contenedor.innerHTML += `
            <div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                
                <img src="${item.imagen}" width="60">

                <div>
                    <b>${item.nombre}</b>
                    <p style="margin:0">S/. ${item.precio}</p>
                    <p style="margin:0">Cant: ${item.cantidad}</p>
                </div>

                <button onclick="eliminar(${item.id})">❌</button>
            </div>
        `;
    });

    // cálculos
    let envio = carrito.length > 0 ? ENVIO : 0;
    let total = subtotal + envio;

    // actualizar UI
    subtotalSpan.textContent = subtotal.toFixed(2);
    envioSpan.textContent = envio.toFixed(2);
    totalFinalSpan.textContent = total.toFixed(2);
    totalSpan.textContent = total.toFixed(2);

    // WhatsApp dinámico
    let mensaje = "Hola NORCENTRO, quiero comprar:%0A";

    carrito.forEach(item => {
        mensaje += `- ${item.nombre} x${item.cantidad} (S/. ${item.precio})%0A`;
    });

    mensaje += `%0ATotal: S/. ${total}`;

    btnWhatsApp.href = `https://wa.me/51922866687?text=${mensaje}`;
}

// ===============================
// ELIMINAR PRODUCTO
// ===============================
function eliminar(id) {

    carrito = carrito.filter(item => item.id !== id);

    localStorage.setItem("carrito", JSON.stringify(carrito));

    renderCarrito();
}

// inicial
renderCarrito();