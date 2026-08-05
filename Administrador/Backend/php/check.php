<?php
// Dice si hay una sesion de personal abierta y si esa persona es admin.
// Lo usan check.js y checkAdmin.js para no dejar entrar a las paginas
// internas escribiendo la direccion a mano.
session_start();
header('Content-Type: application/json; charset=utf-8');

// status: hay alguien del personal logueado (sirve para Usuario.html).
$log   = isset($_SESSION['log'], $_SESSION['rol']) && $_SESSION['log'] === true;
// admin: ademas de estar logueado, su rol es admin (sirve para admin.html).
$admin = $log && $_SESSION['rol'] == 'admin';

echo json_encode(["status" => $log, "admin" => $admin]);


asdasd