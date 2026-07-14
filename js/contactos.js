//12
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('form-contacto');
  if (!form) return;

  const campos = {
    nombre: document.getElementById('nombre'),
    email: document.getElementById('email'),
    telefono: document.getElementById('telefono'),
    asunto: document.getElementById('asunto'),
    mensaje: document.getElementById('mensaje'),
  };

  const errores = {
    nombre: document.getElementById('error-nombre'),
    email: document.getElementById('error-email'),
    telefono: document.getElementById('error-telefono'),
    asunto: document.getElementById('error-asunto'),
    mensaje: document.getElementById('error-mensaje'),
  };

  const mensajeExito = document.getElementById('mensaje-exito');

  // Número de WhatsApp del negocio (formato internacional, sin "+", espacios ni guiones)
  const WHATSAPP_NUMERO = '51900286035';

  const ETIQUETAS_ASUNTO = {
    consulta: 'Consulta general',
    cotizacion: 'Cotización de moto',
    postventa: 'Servicio postventa',
    otro: 'Otro',
  };

  function construirMensajeWhatsApp() {
    const nombre = campos.nombre.value.trim();
    const email = campos.email.value.trim();
    const telefono = campos.telefono.value.trim();
    const asunto = ETIQUETAS_ASUNTO[campos.asunto.value] || campos.asunto.value;
    const mensaje = campos.mensaje.value.trim();

    const texto =
      'Hola NORCENTRO, quisiera hacer una consulta desde la web:\n\n' +
      `*Nombre:* ${nombre}\n` +
      `*Correo:* ${email}\n` +
      `*Teléfono:* ${telefono}\n` +
      `*Asunto:* ${asunto}\n` +
      `*Mensaje:* ${mensaje}`;

    return `https://wa.me/${WHATSAPP_NUMERO}?text=${encodeURIComponent(texto)}`;
  }

  const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const REGEX_TELEFONO = /^[0-9+\-\s()]{6,15}$/;

  function mostrarError(campo, texto) {
    errores[campo].textContent = texto;
    campos[campo].classList.add('campo-invalido');
  }

  function limpiarError(campo) {
    errores[campo].textContent = '';
    campos[campo].classList.remove('campo-invalido');
  }

 function validarNombre() {

    const valor = campos.nombre.value.trim();

    const REGEX_NOMBRE = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;


    if(valor.length < 3){

        mostrarError(
            'nombre',
            'El nombre debe tener al menos 3 caracteres.'
        );

        return false;

    }


    if(!REGEX_NOMBRE.test(valor)){

        mostrarError(
            'nombre',
            'El nombre no debe contener números ni símbolos.'
        );

        return false;

    }


    limpiarError('nombre');

    return true;
}

  function validarEmail() {
    const valor = campos.email.value.trim();
    if (!REGEX_EMAIL.test(valor)) {
      mostrarError('email', 'Ingresa un correo electrónico válido.');
      return false;
    }
    limpiarError('email');
    return true;
  }

  function validarTelefono() {
    const valor = campos.telefono.value.trim();
    if (!REGEX_TELEFONO.test(valor)) {
      mostrarError('telefono', 'Ingresa un teléfono válido (6 a 15 caracteres, solo números y símbolos comunes).');
      return false;
    }
    limpiarError('telefono');
    return true;
  }

  function validarAsunto() {
    const valor = campos.asunto.value;
    if (!valor) {
      mostrarError('asunto', 'Selecciona un asunto.');
      return false;
    }
    limpiarError('asunto');
    return true;
  }

  function validarMensaje() {
    const valor = campos.mensaje.value.trim();
    if(valor.length > 500){

 mostrarError(
 'mensaje',
 'El mensaje no puede superar los 500 caracteres.'
 );

 return false;

}
    if (valor.length < 10) {
      mostrarError('mensaje', 'El mensaje debe tener al menos 10 caracteres.');
      return false;
    }
    limpiarError('mensaje');
    return true;
  }

  // Validación en tiempo real al salir de cada campo
  campos.nombre.addEventListener('blur', validarNombre);
  campos.email.addEventListener('blur', validarEmail);
  campos.telefono.addEventListener('blur', validarTelefono);
  campos.asunto.addEventListener('change', validarAsunto);
  campos.mensaje.addEventListener('blur', validarMensaje);

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const nombreValido = validarNombre();
    const emailValido = validarEmail();
    const telefonoValido = validarTelefono();
    const asuntoValido = validarAsunto();
    const mensajeValido = validarMensaje();

    const formularioValido = nombreValido && emailValido && telefonoValido && asuntoValido && mensajeValido;

    if (formularioValido) {


    const confirmar = confirm(
        "¿Deseas enviar este mensaje a NORCENTRO por WhatsApp?"
    );


    if(confirmar){

        const urlWhatsApp = construirMensajeWhatsApp();

        mensajeExito.classList.remove('oculto');


        // Abre WhatsApp con el mensaje preparado
        window.open(urlWhatsApp, '_blank', 'noopener');


        form.reset();

        Object.keys(campos).forEach(limpiarError);


        setTimeout(function () {
            mensajeExito.classList.add('oculto');
        }, 4000);


    }


}
  });
});