<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

// Validar que se reciba el ID
if (empty($_GET['id'])) {
    die("Error: No se especificó el ID del usuario");
} else {
    $id_usuario = intval($_GET['id']);

    // Opcional: Evitar que el usuario se elimine a sí mismo si está logueado
    // Asumiendo que guardas el ID en $_SESSION['id_usuario'] o similar.
    if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $id_usuario) {
        header("Location: ver-usuarios.php?mensaje=Error: No puedes eliminar tu propia cuenta mientras estás conectado.");
        exit();
    }

    // Intentar eliminar el usuario de la base de datos
    $sql = "DELETE FROM usuario WHERE id_usuario = $id_usuario";

    // Usamos un bloque try-catch o verificamos el error específico para dar feedback útil
    // por si el usuario tiene préstamos activos (Restricción de clave foránea)
    try {
        if (mysqli_query($conn, $sql)) {
            // Redirigir de vuelta a la lista con mensaje de éxito
            header("Location: ver-usuarios.php?mensaje=Usuario eliminado correctamente");
            exit();
        } else {
            // Si hay un error SQL (que no sea excepción en versiones viejas de PHP)
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Detectar si el error es por claves foráneas (ej. el usuario tiene préstamos)
        // El código de error 1451 en MySQL indica restricción de clave foránea
        if (strpos($error, 'foreign key constraint') !== false) {
            $mensaje_error = "Sesion en uso.";
        } else {
            $mensaje_error = "Error al eliminar el usuario: " . $error;
        }

        // Redirigir con el mensaje de error para que se vea bonito en la tabla en lugar de un 'die'
        // O puedes usar die($mensaje_error) si prefieres detener la ejecución.
        header("Location: ver-usuarios.php?mensaje=" . urlencode($mensaje_error));
        exit();
    }
}
?>
