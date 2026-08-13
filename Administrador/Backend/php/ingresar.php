<?php
// Login del personal (admin, mozo y cocinero).
// Recibe email y contrasena por POST y, si estan bien, devuelve el rol
// para que el javascript sepa a que pagina mandarlo.
include("conexion.php");
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
    // La contrasena a proposito no se valida: tiene que poder llevar simbolos,
    // y si esta mal el password_verify de abajo la rechaza igual.
    if (!filter_var($n, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'msg' => 'Usuario no encontrado'
        ]);
        exit;
    }

    // Busca el usuario por email uniendo con personal, por dos motivos:
    // asi solo pueden entrar empleados (un cliente no esta en esa tabla)
    // y de paso se trae el rol, que vive en personal.
    $stmt = $con->prepare("SELECT u.id_usuario, u.nombre, u.contrasena, p.rol
                           FROM usuario u
                           INNER JOIN personal p ON p.id_personal = u.id_usuario
                           WHERE u.email = ?");
    $stmt->execute([$n]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compara la contrasena escrita contra el hash guardado en la base.
    if ($usuario && password_verify($a, $usuario['contrasena'])) {
        // Guarda en la sesion que entro y con que rol.
        // check.php despues lee estos datos para proteger las paginas internas.
        session_start();
        $_SESSION['log'] = true;
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];

        echo json_encode([
            'success' => true,
            'rol' => $usuario['rol'],
            'nombre' => $usuario['nombre']
        ]);
    } else {
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
