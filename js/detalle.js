// =====================================
// DETALLE DE MOTO PROFESIONAL
// =====================================


// -------------------------------
// BOTÓN VOLVER
// -------------------------------

const btnVolver = document.getElementById("btnVolver");


if(btnVolver){

    if(document.referrer.includes("ofertas.html")){

        btnVolver.href = "ofertas.html";

    }else{

        btnVolver.href = "motos.html";

    }

}



// -------------------------------
// OBTENER ID DE LA URL
// -------------------------------

const params = new URLSearchParams(window.location.search);

const id = Number(params.get("id"));


// Buscar moto

const moto = motos.find(m => m.id === id);


// Contenedores

const detalle =
document.getElementById("detalleMoto");

const recomendadas =
document.getElementById("recomendadas");





// -------------------------------
// MOSTRAR DETALLE
// -------------------------------


if(moto){


detalle.innerHTML = `


<div class="detalle-container">


<!-- IMAGEN -->

<div class="galeria">


<img 
class="imagen-principal"
src="${moto.imagen}"
alt="${moto.nombre}">



<div class="miniaturas">

<img src="${moto.imagen}">

<img src="${moto.imagen}">

<img src="${moto.imagen}">
<div class="info-moto">

<h1>
${moto.nombre}
</h1>


<p class="marca-detalle">
${moto.marca} · ${moto.tipo}
</p>


<div class="estrellas">
★★★★★
</div>


<div class="datos-rapidos">


<div>
<strong>
${moto.especificaciones.cilindrada}
</strong>

<span>
Cilindrada
</span>

</div>


<div>

<strong>
${moto.especificaciones.potencia}
</strong>

<span>
Potencia
</span>

</div>


<div>

<strong>
${moto.especificaciones.frenos}
</strong>

<span>
Seguridad
</span>

</div>


</div>



<h2 class="precio-detalle">

S/. ${moto.precio}

</h2>



<div class="acciones">


<button 
class="btn-carrito"
onclick="agregarCarrito(${moto.id})">

🛒 Agregar al carrito

</button>



<button 
class="btn-comprar"
onclick="comprarAhora(${moto.id})">

🟢 Comprar ahora

</button>


</div>



<h3>
Descripción
</h3>


<p>
${moto.descripcion}
</p>



</div>
<div class="caracteristicas">


<h2>
Características destacadas
</h2>


<ul>

<li>
✓ Motor ${moto.especificaciones.motor}
</li>


<li>
✓ Sistema de frenos ${moto.especificaciones.frenos}
</li>


<li>
✓ Transmisión ${moto.especificaciones.transmision}
</li>


<li>
✓ Suspensión ${moto.especificaciones.suspension}
</li>


</ul>


</div>




<!-- ESPECIFICACIONES -->


<div class="especificaciones">


<h2>
Especificaciones técnicas
</h2>


<table>


<tr>

<td>Motor</td>

<td>
${moto.especificaciones.motor}
</td>

</tr>



<tr>

<td>Cilindrada</td>

<td>
${moto.especificaciones.cilindrada}
</td>

</tr>



<tr>

<td>Potencia</td>

<td>
${moto.especificaciones.potencia}
</td>

</tr>



<tr>

<td>Peso</td>

<td>
${moto.especificaciones.peso}
</td>

</tr>



<tr>

<td>Transmisión</td>

<td>
${moto.especificaciones.transmision}
</td>

</tr>



<tr>

<td>Tanque</td>

<td>
${moto.especificaciones.tanque}
</td>

</tr>



<tr>

<td>Frenos</td>

<td>
${moto.especificaciones.frenos}
</td>

</tr>



<tr>

<td>Suspensión</td>

<td>
${moto.especificaciones.suspension}
</td>

</tr>


</table>


</div>


`;





// -------------------------------
// RECOMENDADAS
// -------------------------------


const similares = motos
.filter(m => m.marca === moto.marca && m.id !== moto.id)
.slice(0,4);



recomendadas.innerHTML = `

<h2>
También te puede interesar
</h2>


<div class="recomendadas-grid">


${similares.map(m=>`


<div class="card-mini">


<img src="${m.imagen}">


<h3>
${m.nombre}
</h3>


<p>
S/. ${m.precio}
</p>


<a href="detalle.html?id=${m.id}">
Ver moto
</a>


</div>


`).join("")}



</div>

`;



}

else{


detalle.innerHTML =
"<h2>Moto no encontrada</h2>";

}




// -------------------------------
// COMPRAR AHORA
// -------------------------------


function comprarAhora(id){


let carrito =
JSON.parse(localStorage.getItem("carrito")) || [];



const moto =
motos.find(m=>m.id===id);



const existe =
carrito.find(item=>item.id===id);



if(!existe){


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



window.location.href =
"finalizar-compra.html";


}