<?php
// Guardia de sesion para las paginas internas.
//
// Se incluye ARRIBA DE TODO en las paginas .php del administrador, antes de
// imprimir una sola linea de html. Si no hay sesion valida corta con un
// redirect y el navegador nunca llega a recibir el contenido de la pagina.
//
// Esto es lo que checkAdmin.js no puede hacer: ese corre en el navegador, asi
// que se saltea pidiendo la pagina con curl o desactivando el javascript.
// El chequeo de verdad tiene que pasar en el servidor, que es lo que hace este.

// $soloAdmin en true para las paginas que son unicamente del admin,
// en false para las que puede ver cualquier empleado.
function proteger($soloAdmin = false) {
    // Puede pasar que la pagina ya haya abierto la sesion, por eso se controla
    // antes: llamar session_start() dos veces tira un warning.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Mismos datos que mira check.php: que este logueado y que tenga rol.
    $log = isset($_SESSION['log'], $_SESSION['rol']) && $_SESSION['log'] === true;

    if (!$log || ($soloAdmin && $_SESSION['rol'] !== 'admin')) {
        // El Location se resuelve contra la direccion de la pagina pedida,
        // y el login esta en la misma carpeta que las paginas internas.
        header('Location: registro_interno.html');
        // Sin el exit el php seguiria e imprimiria el html igual.
        exit;
    }
}
