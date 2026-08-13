// Control de entrada para las paginas de cualquier empleado (Usuario.php).
// Le pregunta al php si hay una sesion abierta y, si no la hay, vuelve al login.

fetch("../../Backend/php/check.php")
    .then(res => res.json())
    .then(datos => {
        // status viene en true si hay alguien del personal logueado.
        if (!datos.status) {
            location.href = "registro_interno.html";
        }
    })
    .catch(() => {
        // Si no se puede consultar, por las dudas tampoco lo deja entrar.
        location.href = "registro_interno.html";
    });
