<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

$id_usuario = $_SESSION['usuario'];

// Consultar la base de datos
$sql = "SELECT * FROM usuario WHERE id_usuario = $id_usuario";
$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conn));
}

$usuario = mysqli_fetch_array($resultado);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasenia_actual = $_POST['contrasenia_actual'];
    $contrasenia_nueva = $_POST['contrasenia_nueva'];
    $contrasenia_repetida = $_POST['contrasenia_repetida'];

    // Verificar la contraseña actual
    if (password_verify($contrasenia_actual, $usuario['contrasenia'])) {
        // Verificar que las nuevas contraseñas coinciden
        if ($contrasenia_nueva === $contrasenia_repetida) {
            // Hashear la nueva contraseña
            $hashed_password = password_hash($contrasenia_nueva, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $update_sql = "UPDATE usuario SET contrasenia = '$hashed_password' WHERE id_usuario = $id_usuario";
            if (mysqli_query($conn, $update_sql)) {
                $exito = "Contraseña actualizada correctamente";
            } else {
                $exito = "Error al actualizar la contraseña: " . mysqli_error($conn);
            }
        } else {
            $exito = "Las nuevas contraseñas no coinciden";
        }
    } else {
        $exito = "La contraseña actual es incorrecta";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav>
        <ul>
            <li class="botones-nav">
                <section class="menu-desplegable">
                    <button>☰ Menú</button>
                    <articule class="menu-contenido">
                        <a href="catalogo.php">Ver Catalogo</a>
                        <a href="anadir_libro.php"> Añadir libro</a>
                        <a href="ver-prestamos.php"> Ver Préstamos</a>
                        <a href="ver-alumno.php"> Ver Alumnos</a>
                        <a href="anadir_alumno.php"> Añadir alumno</a>
                        <a href="ver-usuarios.php"> Ver usuarios</a>
                        <a href="anadir_usuarios.php">Añadir usuarios</a>
                        <a href="perfil.php" class="menucuenta"> Perfil</a>
                        <a href="../cerrar_sesion.php" class="cerrarsesion"> Cerrar sesion</a>
                    </articule>
                </section>
            </li>  
            <li class="botones-nav"><h1>Biblioteca<br>Andres Manjon</h1></li>
            <li class="botones-nav">
                <form action="buscar.php">
                    <input type="text" name="buscar" placeholder="Buscar libro..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <main class="detalle-libro-main">
        <articule class="detalle-libro" style="width: 30%; height: 30%; justify-content: center; text-align: center;">
                <div class="form-caja">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="contrasenia">Contraseña actual:</label>
                            <input type="password" id="contrasenia" name="contrasenia_actual">
                        </div>
                        <div class="form-group">
                            <label for="contrasenia">Nueva contraseña :</label>
                            <input type="password" id="contrasenia" name="contrasenia_nueva">
                        </div>
                        <div class="form-group">
                            <label for="contrasenia">Repite nueva contraseña:</label>
                            <input type="password" id="contrasenia" name="contrasenia_repetida">
                        </div>
                        <section class="botones">
                            <button type="submit" class="btn-submit">Cambiar contraseña</button>
                        </section>
                    </form>

                    <section class="botones">
                        <a href="perfil.php"><button>Volver al perfil</button></a>
                    </section>
                </div>
    </articule>
    </main>

    <footer>
        <section id="footer-cabecera">
            <a href="https://ceipandresmanjon.catedu.es"><h2>CEIP Andres Manjon</h2></a>
        </section>
        <section class="footer-section">
            <p>C. de las Delicias, 90, Delicias.</p>
            <p>50017 Zaragoza.</p>
        </section>
        <section class="footer-section">
            <p>Tlf: +34 976 331 728</p>
            <p>Lunes a viernes: 8:30 a 15:00</p>
        </section>
    </footer>
</body>
</html>
