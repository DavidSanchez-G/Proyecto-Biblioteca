<?php
session_start();
// Elimina todas las variables de sesión
$_SESSION = array();
session_unset();
session_destroy(); //elimina la sesión

// Borrar la cookie de sesión (opcional pero recomendable)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),     // Nombre de la cookie (por defecto PHPSESSID)
        '',                 // Valor vacío
        time() - 42000,     // Expira en el pasado
        $params["path"],    // Ruta donde la cookie es válida
        $params["domain"],  // Dominio donde la cookie es válida
        $params["secure"],  // Solo enviar por HTTPS si es true
        $params["httponly"] // Solo accesible vía HTTP (no por JavaScript)
    );
}

header("Location: index.php"); //dirige al login
exit();