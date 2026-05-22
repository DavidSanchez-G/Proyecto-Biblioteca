<?php
require '../conexion.php';

$sql = "SELECT id_alumnado, clase FROM alumnado WHERE codigo_de_carnet IS NULL OR codigo_de_carnet = ''";
$result = mysqli_query($conn, $sql);

while ($alumno = mysqli_fetch_assoc($result)) {
    $id = $alumno['id_alumnado'];
    $clase = $alumno['clase'];
    $codigo_de_carnet = $clase . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
    $update_sql = "UPDATE alumnado SET codigo_de_carnet = '$codigo_de_carnet' WHERE id_alumnado = $id";
    mysqli_query($conn, $update_sql);
}

echo "Códigos de carnet generados para alumnos existentes.";
?>