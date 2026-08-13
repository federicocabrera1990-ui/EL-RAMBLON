<?php
// Login de clientes.
// Recibe email y contrasena por POST y contesta en JSON si pudo entrar o no.
include("conexion.php");


// Le avisa al navegador que lo que devolvemos es JSON.
header('Content-Type: application/json; charset=utf-8');

// Solo seguimos si vino por POST y llegaron los dos campos del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['contrasena'])) {
    // FILTER_SANITIZE_EMAIL borra del email todo caracter que no puede aparecer
    // en una direccion (espacios, comillas, <, >, etc) antes de usarlo.
    $n = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $a = trim($_POST['contrasena']);

    // Lista blanca sobre el email antes de tocar la base. Hace falta aparte del
    // sanitize, porque ese solo borra caracteres y no dice si lo que quedo sirve.
    // Aca no es lo que frena una inyeccion (de eso ya se encarga la consulta
    // preparada de abajo), pero descarta de entrada cualquier cosa que ni
    // siquiera tiene forma de email, asi no se hace una consulta al pedo.
    // Se contesta el mismo mensaje que cuando el usuario no existe, para no
    // darle pistas a alguien que este probando cuentas.
    // La contrasena a proposito no se filtra ni se valida, y no es un olvido:
    // fijate en el SELECT de abajo que la consulta busca SOLO por email. La
    // contrasena nunca entra a una consulta, lo unico que se hace con ella es
    // pasarla a password_verify, que la compara en memoria contra el hash
    // guardado. No hay forma de que inyecte SQL desde ahi. Tampoco se imprime
    // en pantalla en ningun momento, asi que tampoco puede ejecutarse como HTML.
    // Y filtrarle caracteres seria peor: le sacaria los simbolos que la hacen
    // fuerte y ademas dejaria afuera a usuarios ya registrados.
    if (!filter_var($n, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'msg' => 'Usuario no encontrado'
        ]);
        exit;
    }

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
} else {
    // Si no vino por POST o falto alguno de los campos hay que contestar igual.
    // Sin este else el php no imprime nada, el res.json() del javascript falla
    // y el formulario se queda sin mostrar ningun mensaje.
    echo json_encode([
        'success' => false,
        'msg' => 'Faltan datos'
    ]);
}
?>

   