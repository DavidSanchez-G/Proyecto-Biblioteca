<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$buscar_escapado = mysqli_real_escape_string($conn, $query);
$sql = "SELECT * FROM alumnado WHERE LOWER(nombre) LIKE LOWER('%$buscar_escapado%') OR LOWER(apellidos) LIKE LOWER('%$buscar_escapado%') OR LOWER(codigo_de_carnet) LIKE LOWER('%$buscar_escapado%') ORDER BY nombre ASC LIMIT 10";
$result = mysqli_query($conn, $sql);

$alumnos = [];
while ($alumno = mysqli_fetch_assoc($result)) {
    $alumnos[] = $alumno;
}

echo json_encode($alumnos);
?>