<?php
// Login de clientes.
// Recibe email y contrasena por POST y contesta en JSON si pudo entrar o no.
include("conexion.php");


// Le avisa al navegador que lo que devolvemos es JSON.
header('Content-Type: application/json; charset=utf-8');

// Solo seguimos si vino por POST y llegaron los dos campos del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['contrasena'])) {
    $n = trim($_POST['email']);
    $a = trim($_POST['contrasena']);

    // Busca el usuario por email.
    // El INNER JOIN con cliente hace que solo entren clientes: si el id no esta
    // en la tabla cliente (o sea, es personal), la consulta no devuelve nada.
    // Se usa una consulta preparada (el ?) para evitar inyeccion SQL.
    $stmt = $con->prepare("SELECT u.id_usuario, u.nombre, u.contrasena
                           FROM usuario u
                           INNER JOIN cliente c ON c.id_cliente = u.id_usuario
                           WHERE u.email = ?");
    $stmt->execute([$n]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compara la contrasena escrita contra el hash guardado en la base.
    // password_verify sabe leer el hash que genero password_hash al registrarse.
    if ($usuario && password_verify($a, $usuario['contrasena'])) {
        // Abre la sesion y guarda los datos del cliente que entro.
        session_start();
        $_SESSION['log'] = true;
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
       
        echo json_encode([
            'success' => true,
           
        ]);
    } else {
        // Mismo mensaje si el email no existe o si la contrasena esta mal,
        // para no darle pistas a alguien que este probando cuentas.
        echo json_encode([
            'success' => false,
            'msg' => 'Usuario no encontrado'
        ]);
    }
}
?>

   