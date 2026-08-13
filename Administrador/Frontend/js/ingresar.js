// Maneja el formulario de login del personal.
// Segun el rol que devuelve el php, manda a una pagina o a otra.

const form = document.getElementById('g');      // el formulario
const div = document.getElementById('mensaje'); // donde se muestra el mensaje

form.addEventListener('submit', async (event) => {
    // Frena el envio normal del formulario, que recargaria la pagina.
    event.preventDefault();

    // Junta todos los campos del formulario para mandarlos.
    const formData = new FormData(form);

    fetch("../../Backend/php/ingresar.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(datos => {
        console.log(datos);

        if (!datos.success) {
            div.innerHTML = `<h3>${datos.msg}</h3>`;
            return;
        }

        // Los dos niveles de acceso: el admin va al panel completo
        // y el resto del personal (mozo y cocinero) a su propia pagina.
        if (datos.rol === "admin") {
            location.href = "admin.php";
        } else {
            location.href = "Usuario.php";
        }
    })
    // Entra aca si no se pudo hablar con el servidor.
    .catch(error => {
        console.error(error);
        div.innerHTML = '<h3>Error en la conexion</h3>';
    });
});