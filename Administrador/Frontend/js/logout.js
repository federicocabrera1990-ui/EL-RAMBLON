// Maneja el boton de cerrar sesion.
// Lo usan admin.php y Usuario.php.

const salir = document.getElementById('salir');

salir.addEventListener('click', () => {

    fetch("../../Backend/php/logout.php")
        .then(res => res.json())
        .then(datos => {
            console.log(datos);
            // Una vez cerrada la sesion, vuelve a la pantalla de login.
            if (datos.exito) {
                location.href = "registro_interno.html";
            }
        })
        .catch(error => {
            console.error(error);
            // Si fallo la conexion igual lo saca de la pagina interna.
            location.href = "registro_interno.html";
        });

});
