// =================================
// SISTEMA DE CARRITO
// =================================


// AGREGAR PRODUCTO

function agregarCarrito(id){


let carrito =
JSON.parse(localStorage.getItem("carrito")) || [];



const moto =
motos.find(m => m.id === id);



const existe =
carrito.find(item => item.id === id);



if(existe){

existe.cantidad++;

}
else{


carrito.push({

id:moto.id,

nombre:moto.nombre,

precio:moto.precio,

imagen:moto.imagen,

cantidad:1

});


}



localStorage.setItem(
"carrito",
JSON.stringify(carrito)
);



actualizarContador();



alert(
moto.nombre + " agregado al carrito"
);


}




// =================================
// CONTADOR DEL MENU
// =================================


function actualizarContador(){


const carrito =
JSON.parse(localStorage.getItem("carrito")) || [];



const total = carrito.reduce(
(sum,item)=>sum + item.cantidad,
0
);



const contador =
document.getElementById("contadorCarrito");



if(contador){

contador.textContent = total;

}


}




document.addEventListener(
"DOMContentLoaded",
actualizarContador
);