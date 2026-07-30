// Control de entrada para las paginas que son solo del admin
// (admin.html y nuevoEmpleado.html). Si no es admin, vuelve al login.
// Ojo: esto es solo para la pantalla. El permiso de verdad lo controla el php.

fetch("../../Backend/php/check.php")
    .then(res => res.json())
    .then(datos => {
        // admin viene en true solo si el rol guardado en la sesion es admin.
        if (!datos.admin) {
            location.href = "registro_interno.html";
        }
    })
    .catch(() => {
        // Si no se puede consultar, por las dudas tampoco lo deja entrar.
        location.href = "registro_interno.html";
    });
