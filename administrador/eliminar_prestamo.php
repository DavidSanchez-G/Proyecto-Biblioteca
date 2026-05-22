<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

if (isset($_GET['id'])) {
    // Limpiamos el ID por seguridad
    $id_prestamo = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Consulta para eliminar el préstamo
    $sql = "DELETE FROM prestamo WHERE id_prestamo = '$id_prestamo'";

    if (mysqli_query($conn, $sql)) {
        // Redirigir con mensaje de éxito
        header("Location: ver-prestamos.php?mensaje=Préstamo eliminado correctamente");
        exit();
    } else {
        echo "Error al eliminar: " . mysqli_error($conn);
    }
} else {
    // Si no hay ID, volver a la lista
    header("Location: ver-prestamos.php");
    exit();
}
?>