<?php
// Conexion a la base de datos con PDO.
// Todos los demas archivos php del backend incluyen este.

// Servidor local, base el_ramblon, usuario root sin contrasena (valores por defecto de XAMPP).
$con=new PDO("mysql:host=localhost;dbname=el_ramblon;charset=utf8mb4","root","");
// Hace que PDO tire una excepcion si algo falla, asi los try/catch la pueden atrapar.
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>