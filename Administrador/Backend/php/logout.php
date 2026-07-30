<?php
// Cierra la sesion del personal.
// Lo llama logout.js cuando se aprieta el boton de salir.
session_start();
header('Content-Type: application/json; charset=utf-8');

// Borra los datos guardados en la sesion y despues la destruye en el servidor.
// Con esto check.php ya no la reconoce y las paginas internas dejan de dejar entrar.
$_SESSION = array();
session_destroy();

echo json_encode(["exito" => true]);
