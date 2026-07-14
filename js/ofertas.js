// ===============================
// OFERTAS DINÁMICAS
// ===============================


const contenedorOfertas =
document.getElementById("contenedorOfertas");


// motos seleccionadas para oferta
const ofertas = motos.slice(0,6);



contenedorOfertas.innerHTML = ofertas.map(m => {

let precioOferta = m.precioOferta;

// HACK: guardar precio de oferta en el link para que al ver "Ver oferta" se mantenga
// el precio con descuento (sin tocar el botón ni estilos existentes).
const urlOferta = `detalle.html?id=${m.id}&oferta=1`;

return `

<div class="col-12 col-sm-6 col-lg-4">

<article class="card-moto card-oferta">


<span class="etiqueta">
Oferta
</span>


<div class="card-media">

<img src="${m.imagen}" 
alt="${m.nombre}">

</div>



<div class="card-body">


<h3>
${m.nombre}
</h3>


<p class="meta">
${m.tipo} · ${m.marca}
</p>



<p class="precio-anterior">
S/. ${m.precio}
</p>


<p class="precio-oferta">
S/. ${precioOferta.toFixed(0)}
</p>



<a href="${urlOferta}"
class="btn-dark"> 

Ver oferta

</a>


</div>


</article>

</div>


`;


}).join("");

