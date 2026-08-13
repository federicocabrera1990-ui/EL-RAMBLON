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

// Controla que el email tenga forma de email. El campo del formulario no alcanza,
// porque se saltea mandando un POST directo a este archivo sin pasar por la pagina.
if (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["exito"=>false]);
    exit;
}

// De aca para abajo se validan los campos con listas blancas: en vez de buscar
// los caracteres peligrosos, se dice cuales son los aceptados y se rechaza todo
// lo demas. Es mas seguro que una lista negra porque no hay que adivinar todas
// las formas que puede tomar un ataque.

// Nombre y apellido: letras, espacios, apostrofe y guion.
// El \p{L} junto con la /u del final toma tambien acentos y ñ, asi entran
// nombres como Jose Maria o Perez-Gomez. El largo acompaña al VARCHAR(50).
$patronNombre = '/^[\p{L}\s\'-]{2,50}$/u';
if (!preg_match($patronNombre, trim($_POST['nombre'])) || !preg_match($patronNombre, trim($_POST['apellido']))) {
    echo json_encode(["exito"=>false]);
    exit;
}

// Telefono: numeros, el + de los codigos de pais, espacios y guiones.
// El largo acompaña al VARCHAR(20) de la base.
if (!preg_match('/^[0-9+\s-]{6,20}$/', trim($_POST['telefono']))) {
    echo json_encode(["exito"=>false]);
    exit;
}

// La contrasena a proposito no lleva lista blanca: tiene que poder llevar
// simbolos. Ademas no se guarda tal cual, se guarda su hash.

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