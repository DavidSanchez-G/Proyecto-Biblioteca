<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

// Validar que se reciba el ID
if (empty($_GET['id'])) {
    die("Error: No se especificó el ID del libro");
} else {
    $id_libro = intval($_GET['id']);
    //1. Eliminamos portada primero si el libro no esta repetido y si la imagen no es la predefinida
    //comprobamos si hay varias copias del mismo libro
    $sql_libro = "SELECT * FROM libro WHERE id_libro = $id_libro";
    $libros = mysqli_query($conn, $sql_libro);
    $libro = mysqli_fetch_array($libros);
    $isbn = $libro['isbn'];
    $sql_duplicado = "SELECT * FROM libro WHERE isbn = '$isbn'";
    $duplicado = mysqli_query($conn, $sql_duplicado);
    //solo eliminamos portada si no hay duplicados
    if (mysqli_num_rows($duplicado) === 1) { 
        // 1. Obtener el nombre de la imagen actual
        $imagen_sql = "SELECT portada FROM libro WHERE id_libro = $id_libro";
        $result = mysqli_query($conn, $imagen_sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $nombre_portada = $row['portada']; // Ej: imagenes/foto1.jpg
            $ruta_imagen = '../' . $nombre_portada; // Ej: ../imagenes/foto1.jpg

            // 2. Comprobación de seguridad:
            // Solo eliminamos si el archivo existe Y NO es la imagen predefinida
            if ($nombre_portada != 'imagenes/predefinido.png' && file_exists($ruta_imagen)) {
                unlink($ruta_imagen); // Eliminar la imagen del servidor
            }
        }
    }
    // 3. Eliminar el registro del libro de la base de datos
    $sql = "DELETE FROM libro WHERE id_libro = $id_libro";
    
    if (mysqli_query($conn, $sql)) {
        // Redirigir de vuelta al catálogo con un mensaje de éxito
        header("Location: catalogo.php?mensaje=Libro eliminado correctamente");
        exit();
    } else {
        die("Error al eliminar el libro: " . mysqli_error($conn));
    }
}
?>