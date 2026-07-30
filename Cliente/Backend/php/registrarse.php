<?php
// Registro de clientes nuevos.
// Recibe los datos por POST, crea el usuario y lo marca como cliente.
// No pide sesion iniciada porque el cliente se registra solo.
include("conexion.php");
header('Content-Type: application/json; charset=utf-8');

// si falta alguno de los campos. Sin este control PHP tira warnings
// que se mezclan con la respuesta y la dejan de ser JSON valido.
if (!isset($_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['email'], $_POST['contrasena'])) {
    echo json_encode(["exito"=>false]);
    exit;
}

// si los campos llegaron vacios. Hace falta aparte del isset de arriba
// porque MySQL acepta cadena vacia en una columna NOT NULL y guardaria basura.
if (trim($_POST['nombre'])=='' || trim($_POST['apellido'])=='' || trim($_POST['telefono'])=='' || trim($_POST['email'])=='' || $_POST['contrasena']=='') {
    echo json_encode(["exito"=>false]);
    exit;
}

try{
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $n = trim($_POST['email']);
    // Nunca se guarda la contrasena tal cual: se guarda su hash.
    $a = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    

    // Son dos INSERT que van juntos, por eso se usa una transaccion:
    // o se guardan los dos, o no se guarda ninguno. Asi no queda un usuario
    // suelto en la tabla usuario sin su fila en cliente.
    $con->beginTransaction();

    // Primero los datos generales, en la tabla usuario.
    $stmt = $con->prepare("INSERT INTO usuario(nombre,apellido,telefono,email,contrasena) VALUES (?,?,?,?,?)");
    $stmt->execute([$nombre,$apellido,$telefono,$n,$a]);

    // Despues se marca como cliente, usando el id que acaba de generar el INSERT anterior.
    $stmt = $con->prepare("INSERT INTO cliente(id_cliente) VALUES (?)");
    $stmt->execute([$con->lastInsertId()]);

    // Confirma los dos INSERT.
    $con->commit();


    echo json_encode(["exito"=>true]);
}catch(PDOException $e){
    // Si algo fallo (por ejemplo un email repetido, que la base no permite),
    // se deshacen los INSERT que hayan quedado a medias.
    if ($con->inTransaction()) { $con->rollBack(); }
    echo json_encode(["exito"=>false]);
}

?>