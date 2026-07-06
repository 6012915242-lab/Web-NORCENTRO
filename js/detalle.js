// ===============================
// VOLVER SEGÚN PÁGINA ANTERIOR
// ===============================

const btnVolver = document.getElementById("btnVolver");


if(btnVolver){

    if(document.referrer.includes("ofertas.html")){

        btnVolver.href = "ofertas.html";

    }
    else{

        btnVolver.href = "motos.html";

    }

}// ===============================
// DETALLE DINÁMICO PROFESIONAL
// ===============================


const params = new URLSearchParams(window.location.search);

const id = Number(params.get("id"));


const moto = motos.find(m => m.id === id);


const contenedor = document.getElementById("detalleMoto");


if(moto){


contenedor.innerHTML = `


<div class="detalle-container">


    <div class="galeria">

        <img 
        class="imagen-principal"
        src="${moto.imagen}"
        alt="${moto.nombre}">


    </div>



    <div class="info-moto">


        <h1>${moto.nombre}</h1>


        <h2>
        S/. ${moto.precio}
        </h2>



        <button class="btn-whatsapp">
        🟢 Comprar por WhatsApp
        </button>



        <h3>Descripción</h3>

        <p>
        ${moto.descripcion || 
        "Motocicleta de excelente rendimiento, diseñada para ofrecer comodidad y seguridad."}
        </p>



        <h3>Especificaciones técnicas</h3>


        <table>


        <tr>
        <td>Marca</td>
        <td>${moto.marca}</td>

        <td>Tipo</td>
        <td>${moto.tipo}</td>
        </tr>



        <tr>
        <td>Motor</td>
        <td>
        ${moto.especificaciones?.motor || "No disponible"}
        </td>

        <td>Cilindrada</td>
        <td>
        ${moto.especificaciones?.cilindrada || "No disponible"}
        </td>
        </tr>



        <tr>
        <td>Potencia</td>
        <td>
        ${moto.especificaciones?.potencia || "No disponible"}
        </td>

        <td>Peso</td>
        <td>
        ${moto.especificaciones?.peso || "No disponible"}
        </td>
        </tr>



        <tr>
        <td>Transmisión</td>
        <td>
        ${moto.especificaciones?.transmision || "No disponible"}
        </td>

        <td>Tanque</td>
        <td>
        ${moto.especificaciones?.tanque || "No disponible"}
        </td>
        </tr>



        </table>


    </div>


</div>


`;



}
else{

contenedor.innerHTML =
"<h2>Moto no encontrada</h2>";

}