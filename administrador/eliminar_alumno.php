<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

// Validar que se reciba el ID
if (empty($_GET['id'])) {
    die("Error: No se especificó el ID del Alumno");
} else {
    $id_alumnado = intval($_GET['id']);

    // Primero eliminar todos los préstamos del alumno
    $sql_prestamos = "DELETE FROM prestamo WHERE id_alumnado = $id_alumnado";
    if (!mysqli_query($conn, $sql_prestamos)) {
        die("Error al eliminar préstamos del alumno: " . mysqli_error($conn));
    }

    // Luego eliminar el alumno de la base de datos
    $sql = "DELETE FROM alumnado WHERE id_alumnado = $id_alumnado";
    if (mysqli_query($conn, $sql)) {
        // Redirigir de vuelta a ver alumnos con un mensaje de éxito
        header("Location: ver-alumno.php?mensaje=Alumno eliminado correctamente");
        exit();
    } else {
        die("Error al eliminar el Alumno: " . mysqli_error($conn));
    }
}
?>