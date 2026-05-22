<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

if (!isset($_GET['id'])) {
    die("Error: Faltan datos.");
}

$id_alumno = mysqli_real_escape_string($conn, $_GET['id']);

// INTERRUPTOR SQL: Cambia de 0 a 1 y de 1 a 0
$sql_update = "UPDATE alumnado 
               SET estado_de_sancion = CASE 
                   WHEN estado_de_sancion = 1 THEN 0 
                   ELSE 1 
               END 
               WHERE id_alumnado = '$id_alumno'";

if (mysqli_query($conn, $sql_update)) {
    // Comprobar cómo quedó para el mensaje
    $check = mysqli_query($conn, "SELECT estado_de_sancion, nombre FROM alumnado WHERE id_alumnado='$id_alumno'");
    $datos = mysqli_fetch_assoc($check);
    
    $mensaje = ($datos['estado_de_sancion'] == 1) 
        ? "El alumno " . $datos['nombre'] . " ha sido SANCIONADO." 
        : "Sanción editada " . $datos['nombre'] . "";

    // TRUCO ANTI-CACHÉ: Añadimos time() al final
    header("Location: ver-alumno.php?mensaje=" . urlencode($mensaje) . "&nocache=" . time());
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>