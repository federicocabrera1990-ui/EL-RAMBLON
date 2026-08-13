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

// Controla que el email tenga forma de email. El type="email" del html no alcanza,
// porque se saltea mandando un POST directo a este archivo sin pasar por el formulario.
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

// Rol: aca la lista blanca es de valores enteros, no de caracteres, porque los
// roles posibles son tres y estan fijos. Repite el CHECK que ya tiene la base,
// pero permite rechazarlo antes de abrir la transaccion.
if (!in_array($_POST['rol'], ['admin','mozo','cocinero'], true)) {
    echo json_encode(["exito"=>false]);
    exit;
}

// La contrasena a proposito no lleva lista blanca: tiene que poder llevar
// simbolos. Ademas no se guarda tal cual, se guarda su hash.


    
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