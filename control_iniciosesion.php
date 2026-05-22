<?php
session_start();
include("conexion.php");

// Recogemos usuario y contraseña 
$usuario_input = htmlspecialchars($_POST["usuario"]);
$pass = htmlspecialchars($_POST["password"]);

// Buscamos el usuario en BD con Prepared Statement
$stmt = $conn->prepare("SELECT * FROM usuario WHERE username = ?");
$stmt->bind_param("s", $usuario_input); 
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 1) {
    $datos_usuario = $resultado->fetch_assoc();
    
    // Verificamos la contraseña
    if (password_verify($pass, $datos_usuario['contrasenia'])) {
       
        // CORRECCIÓN AQUÍ: Consultamos el rol usando el id_rol del usuario
        $id_rol = $datos_usuario['id_rol'];
        $sql_rol = "SELECT nombre FROM rol WHERE id_rol = '$id_rol'"; 
        $res_rol = mysqli_query($conn, $sql_rol);
        $datos_rol = $res_rol->fetch_assoc();
        
        // Guardamos en sesión el nombre del rol
        $nombre_rol = $datos_rol["nombre"]; 
        $_SESSION["rol"] = $nombre_rol;
        $_SESSION["usuario"] = $datos_usuario["id_usuario"];

        // CORRECCIÓN EN LAS COMPARACIONES:
        // Usamos la variable $nombre_rol que viene de la columna 'nombre'
        if ($nombre_rol == "admin") {
            header("location: administrador/catalogo.php");
            exit();
        } elseif ($nombre_rol == "profesor") {
            header("location: usuario/catalogo.php");
            exit();
        } else {
            $_SESSION["inicio"] = "Rol no reconocido: " . $nombre_rol;
            header("location: iniciosesion.php");
            exit();
        }
    } else {
        $_SESSION["inicio"] = "Contraseña incorrecta";
        header("location: iniciosesion.php");
        exit();
    }
} else {
    $_SESSION["inicio"] = "Usuario incorrecto";
    header("location: iniciosesion.php");
    exit();
}

$stmt->close();
$conn->close();
?>