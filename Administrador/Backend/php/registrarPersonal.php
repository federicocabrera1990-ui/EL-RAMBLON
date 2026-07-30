<?php 
// Alta de empleados. Solo la puede usar un admin.
// Recibe los datos por POST, crea el usuario y lo marca como personal con su rol.
session_start();
include("conexion.php");
header('Content-Type: application/json; charset=utf-8');

// Control de permisos del lado del servidor.
// checkAdmin.js esconde la pantalla, pero eso es solo visual: sin este if
// cualquiera podria mandar un POST a este archivo y crearse un admin.
if (!isset($_SESSION['log'], $_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(["exito"=>false]);
    exit;
}

// si falta alguno de los campos, para que PHP no tire warnings
// que se mezclen con la respuesta y la dejen de ser JSON valido.
if (!isset($_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['email'], $_POST['contrasena'], $_POST['rol'])) {
    echo json_encode(["exito"=>false]);
    exit;
}

// si los campos llegaron vacios. Hace falta aparte del isset de arriba
// porque MySQL acepta cadena vacia en una columna NOT NULL y guardaria empleados en blanco.
if (trim($_POST['nombre'])=='' || trim($_POST['apellido'])=='' || trim($_POST['telefono'])=='' || trim($_POST['email'])=='' || $_POST['contrasena']=='' || $_POST['rol']=='') {
    echo json_encode(["exito"=>false]);
    exit;
}


    
    try{
    $n        = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $email    = trim($_POST['email']);
    $rol      = $_POST['rol'];
    // Nunca se guarda la contrasena tal cual: se guarda su hash.
    $a = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    

    // Son dos INSERT que van juntos, por eso se usa una transaccion:
    // o se guardan los dos, o no se guarda ninguno.
    $con->beginTransaction();

    // Primero los datos generales, en la tabla usuario.
    $stmt = $con->prepare("INSERT INTO usuario(nombre,apellido,telefono,email,contrasena) VALUES (?,?,?,?,?)");
    $stmt->execute([$n,$apellido,$telefono,$email,$a]);

    // Despues se marca como personal con su rol, usando el id recien generado.
    // Si el rol no es admin, mozo o cocinero, la base lo rechaza por el CHECK.
    $stmt = $con->prepare("INSERT INTO personal(id_personal,rol) VALUES (?,?)");
    $stmt->execute([$con->lastInsertId(),$rol]);

    // Confirma los dos INSERT.
    $con->commit();


    echo json_encode(["exito"=>true]);
}catch(PDOException $e){
    // Si algo fallo (email repetido o rol invalido), se deshacen
    // los INSERT que hayan quedado a medias.
    if ($con->inTransaction()) { $con->rollBack(); }
    echo json_encode(["exito"=>false]);
}

?>