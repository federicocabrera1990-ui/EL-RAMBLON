// Maneja el formulario de registro del cliente.
// Manda los datos al php y avisa si se pudo crear la cuenta.

const registrarse = document.getElementById('registrarse'); // el formulario
const div= document.getElementById('asd2');                 // donde se muestra el mensaje

    registrarse.addEventListener("submit", (e) => {
    // Frena el envio normal del formulario, que recargaria la pagina.
    e.preventDefault();

    // Junta todos los campos del formulario para mandarlos.
    let form = new FormData(registrarse);

    fetch("../../../Backend/php/registrarse.php",{
        method: "POST",
        body: form
    })
    
    .then(res => res.json())
    .then(datos=>{
        console.log(datos);

    
        // exito lo manda el php: true si pudo guardar el usuario.
        if (datos.exito) {
            div.innerHTML = '<h3>Agregado</h3>';

        } else {
            // Falla si el email ya existe o si algun campo llego vacio.
            div.innerHTML = '<h3>Error</h3>';

        }
    })
})