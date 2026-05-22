<?php

if (isset($_SESSION["rol"])) {
    $rol = $_SESSION["rol"];
    $usuario = $_SESSION["usuario"];

    if ($rol != "admin") {
    session_unset();
    session_destroy();
    header("location: ../iniciosesion.php");
    exit;
    }

} else {
    session_unset();
    session_destroy();
    header("location: ../iniciosesion.php");
    exit;
}
