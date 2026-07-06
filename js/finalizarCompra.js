// =================================
// CARRITO PROFESIONAL
// =================================


let carrito =
JSON.parse(localStorage.getItem("carrito")) || [];



const contenedor =
document.getElementById("carritoContenedor");



const subtotalHTML =
document.getElementById("subtotal");


const totalHTML =
document.getElementById("total");


const totalFinalHTML =
document.getElementById("totalFinal");


const btnWhatsApp =
document.getElementById("btnWhatsApp");




// MOSTRAR CARRITO


function mostrarCarrito(){


if(carrito.length === 0){


contenedor.innerHTML = `

<div class="carrito-vacio">

<h3>
Tu carrito está vacío
</h3>


<a href="motos.html">
Ver motos
</a>

</div>

`;


actualizarTotales(0);

return;

}



contenedor.innerHTML = carrito.map(item=>{


return `


<div class="producto-carrito">


<img src="${item.imagen}">



<div class="producto-info">


<h4>
${item.nombre}
</h4>



<p>
Precio:
S/. ${item.precio}
</p>



<div class="cantidad">


<button onclick="restar(${item.id})">
−
</button>


<span>
${item.cantidad}
</span>


<button onclick="sumar(${item.id})">
+
</button>


</div>



<button 
class="btn-eliminar"
onclick="eliminar(${item.id})">

Eliminar

</button>



</div>



</div>


`;


}).join("");



calcularTotal();


}





// SUMAR


function sumar(id){


const producto =
carrito.find(p=>p.id===id);


producto.cantidad++;


guardar();


}




// RESTAR


function restar(id){


const producto =
carrito.find(p=>p.id===id);



if(producto.cantidad > 1){

producto.cantidad--;

}
else{

eliminar(id);

return;

}



guardar();


}




// ELIMINAR


function eliminar(id){


carrito =
carrito.filter(
p=>p.id!==id
);


guardar();


}





// GUARDAR


function guardar(){


localStorage.setItem(
"carrito",
JSON.stringify(carrito)
);


mostrarCarrito();


}





// CALCULAR TOTAL


function calcularTotal(){


let subtotal = 0;



carrito.forEach(item=>{


subtotal +=
item.precio * item.cantidad;


});



actualizarTotales(subtotal);



crearWhatsApp(subtotal);



}




function actualizarTotales(sub){


let envio = 50;


subtotalHTML.textContent =
sub.toFixed(2);



totalHTML.textContent =
sub.toFixed(2);



totalFinalHTML.textContent =
(sub+envio).toFixed(2);


}




// WHATSAPP


function crearWhatsApp(sub){


let mensaje =
"Hola NORCENTRO, deseo realizar una compra:%0A%0A";



carrito.forEach(item=>{


mensaje +=
`${item.nombre} x${item.cantidad} - S/.${item.precio}%0A`;


});



mensaje +=
`%0ASubtotal: S/.${sub}`;



btnWhatsApp.href =
"https://wa.me/51922866687?text="
+mensaje;


}





mostrarCarrito();