<?php
$servidor = "localhost";       // Coincide con @'localhost' del comando SQL
$usuario  = "root";         // Coincide con 'skyland'
$password = "";      // Coincide con 'biblioteca'
$base     = "sistemabiblioteca"; // Coincide con CREATE DATABASE

$conn = mysqli_connect($servidor, $usuario, $password, $base);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
