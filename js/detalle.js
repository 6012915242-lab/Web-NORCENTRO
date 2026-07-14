// =====================================
// DETALLE DE MOTO PROFESIONAL
// =====================================


// -------------------------------
// BOTÓN VOLVER
// -------------------------------

const btnVolver = document.getElementById("btnVolver");

if (btnVolver) {

    if (document.referrer.includes("ofertas.html")) {
        btnVolver.href = "ofertas.html";
    } else {
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

const detalle = document.getElementById("detalleMoto");
const recomendadas = document.getElementById("recomendadas");


// -------------------------------
// MOSTRAR DETALLE
// -------------------------------

if (moto) {

    detalle.innerHTML = `

        <div class="detalle-container">

            <!-- IMAGEN -->

            <div class="galeria">

                <img
                    class="imagen-principal"
                    src="${moto.imagen}"
                    alt="${moto.nombre}"
                >

                <div class="miniaturas">

                    ${
                        (moto.imagenes || [moto.imagen])
                            .map(img => `
                                <img
                                    src="${img}"
                                    alt="${moto.nombre}"
                                    onclick="cambiarImagen('${img}')"
                                >
                            `)
                            .join("")
                    }

                </div>

            </div>


            <!-- INFORMACIÓN -->

            <div class="info-moto">

                <h1>${moto.nombre}</h1>

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

${
    moto.precioOferta
    ?
    `
        <span class="precio-anterior">
            S/. ${moto.precio}
        </span>

        <span class="precio-oferta">
            S/. ${moto.precioOferta}
        </span>
    `
    :
    `
        <span class="precio-normal">
            S/. ${moto.precio}
        </span>
    `
}

</h2>

                <div class="acciones">

                    <a
                        class="btn-comprar"
                        href="https://wa.me/51900286035?text=Hola%20NORCENTRO,%20deseo%20información%20sobre%20la%20${encodeURIComponent(moto.nombre)}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i class="fa-brands fa-whatsapp"></i>
                        Más información

                    </a>

                </div>


                <h3>Descripción</h3>

                <p>
                    ${moto.descripcion}
                </p>

            </div>

        </div>


        <!-- =================================
             FICHA TÉCNICA DOS COLUMNAS
        ==============================<h2 class="precio-detalle">==== -->

        <div class="ficha-tecnica">

            <div class="bloque-ficha">

                <h2>Características destacadas</h2>

                <ul>
                    <li>✓ Motor ${moto.especificaciones.motor}</li>
                    <li>✓ Sistema de frenos ${moto.especificaciones.frenos}</li>
                    <li>✓ Transmisión ${moto.especificaciones.transmision}</li>
                    <li>✓ Suspensión ${moto.especificaciones.suspension}</li>
                </ul>

            </div>


            <div class="bloque-ficha">

                <h2>Especificaciones técnicas</h2>

                <table>

                    <tr>
                        <td>Motor</td>
                        <td>${moto.especificaciones.motor}</td>
                    </tr>

                    <tr>
                        <td>Cilindrada</td>
                        <td>${moto.especificaciones.cilindrada}</td>
                    </tr>

                    <tr>
                        <td>Potencia</td>
                        <td>${moto.especificaciones.potencia}</td>
                    </tr>

                    <tr>
                        <td>Peso</td>
                        <td>${moto.especificaciones.peso}</td>
                    </tr>

                    <tr>
                        <td>Transmisión</td>
                        <td>${moto.especificaciones.transmision}</td>
                    </tr>

                    <tr>
                        <td>Tanque</td>
                        <td>${moto.especificaciones.tanque}</td>
                    </tr>

                    <tr>
                        <td>Frenos</td>
                        <td>${moto.especificaciones.frenos}</td>
                    </tr>

                    <tr>
                        <td>Suspensión</td>
                        <td>${moto.especificaciones.suspension}</td>
                    </tr>

                </table>

            </div>

        </div>
    `;


    // -------------------------------
    // RECOMENDADAS
    // -------------------------------

    const similares = motos
        .filter(m => m.marca === moto.marca && m.id !== moto.id)
        .slice(0, 4);


    if (recomendadas) {

        recomendadas.innerHTML = `

            <h2>También te puede interesar</h2>

            <div class="recomendadas-grid">

                ${
                    similares.map(m => `

                        <div class="card-mini">

                            <img
                                src="${m.imagen}"
                                alt="${m.nombre}"
                            >

                            <h3>${m.nombre}</h3>

                           <p>

${
    m.precioOferta
    ?
    `
        <span class="precio-anterior">
            S/. ${m.precio}
        </span>

        <span class="precio-oferta">
            S/. ${m.precioOferta}
        </span>
    `
    :
    `
        S/. ${m.precio}
    `
}

</p>

                            <a href="detalle.html?id=${m.id}">
                                Ver moto
                            </a>

                        </div>

                    `).join("")
                }

            </div>
        `;

    }

} else {

    if (detalle) {
        detalle.innerHTML = "<h2>Moto no encontrada</h2>";
    }

}


// -------------------------------
// CAMBIAR IMAGEN PRINCIPAL
// -------------------------------

function cambiarImagen(img) {

    const principal = document.querySelector(".imagen-principal");

    if (principal) {
        principal.src = img;
    }

}