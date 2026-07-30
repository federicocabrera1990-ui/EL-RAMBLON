// Maneja el formulario de alta de empleados (solo lo usa el admin).
// Manda los datos al php y avisa si se pudo crear el empleado.

const f = document.getElementById("f")          // el formulario
const div = document.getElementById("admin1")   // donde se muestra el mensaje

    f.addEventListener("submit", (e) => {

    // Frena el envio normal del formulario, que recargaria la pagina.
    e.preventDefault();

    // Junta todos los campos del formulario para mandarlos.
    let form = new FormData(f)

    fetch("../../Backend/php/registrarPersonal.php",
       {method: "post",
        body: form
})
    .then(res => res.json())
    .then(datos => {
        console.log(datos);
        // exito lo manda el php: true si pudo guardar el empleado.
        if (datos.exito) {
            div.innerHTML = '<h3>Agregado</h3>';

        } else {
            // Falla si el email ya existe, si falto algun campo,
            // o si quien lo pidio no es un admin logueado.
            div.innerHTML = '<h3>Error</h3>';

        }

    });

 });