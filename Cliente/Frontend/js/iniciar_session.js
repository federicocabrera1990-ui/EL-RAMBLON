// Maneja el formulario de login del cliente.
// Manda los datos al php y muestra el resultado sin recargar la pagina.

const g = document.getElementById('g');     // el formulario
const div = document.getElementById('asd'); // donde se muestra el mensaje

    g.addEventListener('submit', (e) => {
    // Frena el envio normal del formulario, que recargaria la pagina.
    e.preventDefault();

    // Junta todos los campos del formulario para mandarlos.
    let form = new FormData(g)

    fetch("../../../Backend/php/inciar_session.php",
       {method: "post",
        body: form
})
    .then(res => res.json()) // convierte la respuesta del php en objeto
    .then(datos => {
        console.log(datos);
        // Si el php dijo que no, muestra el mensaje de error que mando.
         if (!datos.success) {
            div.innerHTML = `<h3>${datos.msg}</h3>`;
            return;
        }

        // Si llego hasta aca, el login salio bien.
        div.innerHTML = '<h3>Accedio</h3>';

    });

 });
