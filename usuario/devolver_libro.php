<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

// Verificamos si recibimos el ID del préstamo
if (isset($_GET['id'])) {
    
    // Limpiamos el ID para seguridad (evitar inyecciones SQL básicas)
    $id_prestamo = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Obtenemos la fecha actual
    $fecha_actual = date('Y-m-d');

    // Actualizamos el préstamo:
    // 1. Cambiamos estado a 'Devuelto'
    // 2. Actualizamos la fecha_de_devolucion al día de hoy (fecha real de entrega)
    $sql = "UPDATE prestamo 
            SET estado_del_prestamo = 'Devuelto', 
                fecha_de_devolucion = '$fecha_actual' 
            WHERE id_prestamo = '$id_prestamo'";
    $sql_disponible = "UPDATE libro SET estado_de_actividad = '0' 
                        WHERE id_libro = (SELECT id_libro FROM prestamo WHERE id_prestamo = '$id_prestamo')";
    if (mysqli_query($conn, $sql) && mysqli_query($conn, $sql_disponible)) {
        // Si sale bien, volvemos a la lista con mensaje de éxito
        header("Location: ver-prestamos.php?mensaje=Libro marcado como devuelto correctamente");
        exit();
    } else {
        // Si sale mal, mostramos el error
        echo "Error al actualizar: " . mysqli_error($conn);
    }

} else {
    // Si intentan entrar sin ID, los mandamos fuera
    header("Location: ver-prestamos.php");
    exit();
}
?>