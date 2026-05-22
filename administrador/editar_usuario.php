<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

$mensaje = "";
$error = "";

// 1. LÓGICA PARA GUARDAR CAMBIOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario']);
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $id_rol = intval($_POST['id_rol']);
    $nueva_contra = $_POST['contrasenia'];

    // Validar que el nombre de usuario no lo tenga OTRA persona
    $sql_check = "SELECT id_usuario FROM usuario WHERE username = '$username' AND id_usuario != $id_usuario";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        $error = "El nombre de usuario '$username' ya está en uso.";
    } else {
        // Preparar actualización
        $sql = "UPDATE usuario SET 
                nombre = '$nombre', 
                username = '$username',  
                id_rol = $id_rol";

        // Si escribió contraseña nueva, la encriptamos y actualizamos
        if (!empty($nueva_contra)) {
            $pass_hash = password_hash($nueva_contra, PASSWORD_DEFAULT);
            $sql .= ", contrasenia = '$pass_hash'";
        }

        $sql .= " WHERE id_usuario = $id_usuario";

        if (mysqli_query($conn, $sql)) {
            // Actualizar el codigo_de_carnet 
            // Generar codigo_de_carnet: rol + id formateado, para ello buscamos el rol del usuario
            $sql_rol = "SELECT nombre FROM rol WHERE id_rol = $id_rol";
            $roles = mysqli_query($conn, $sql_rol);
            $rol= mysqli_fetch_assoc($roles);
            switch ($rol["nombre"]) {
                case "admin":
                    $codigo_rol = "0AD";
                    break;
                case "profesor":
                    $codigo_rol = "1PF";
                    break;
                case "alumno":
                    $codigo_rol = "2EN";
                    break;
            }
            // Generamos el codigo_de_carnet
            $codigo_de_carnet = $codigo_rol . '-' . str_pad($id_usuario, 5, '0', STR_PAD_LEFT);
            $update_sql = "UPDATE usuario SET codigo_de_carnet = '$codigo_de_carnet' WHERE id_usuario = $id_usuario";
            mysqli_query($conn, $update_sql);
            $exito = "Usuario añadido correctamente con código de carnet: $codigo_de_carnet";

            // Redirigir con éxito
            header("Location: ver-usuarios.php?mensaje=Usuario modificado correctamente");
            exit();
        } else {
            $error = "Error al actualizar: " . mysqli_error($conn);
        }
    }
}

// 2. LÓGICA PARA CARGAR DATOS (GET)
if (isset($_GET['id'])) {
    $id_usuario = intval($_GET['id']);
    $sql = "SELECT * FROM usuario WHERE id_usuario = $id_usuario";
    $resultado = mysqli_query($conn, $sql);
    $usuario = mysqli_fetch_assoc($resultado);

    if (!$usuario) {
        header("Location: ver-usuarios.php?mensaje=Usuario no encontrado");
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si entran sin ID y no es POST, fuera
    header("Location: ver-usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Biblioteca</title>
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
                <form action="ver-usuarios.php" method="GET">
                    <input type="text" name="busqueda" placeholder="Buscar usuario..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="contenedor-formulario">
        <div class="form-caja">
            <h2 style="text-align: center; color: #602b06; margin-top: 0;">Editar Usuario</h2>
            
            <?php if (!empty($error)): ?>
                <div class="mensaje-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="editar_usuario.php" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">

                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Usuario (Login):</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="contrasenia">Contraseña:</label>
                    <input type="password" id="contrasenia" name="contrasenia" placeholder="******">
                    <div class="nota-aviso">⚠️ No toques este campo si no quieres cambiar la contraseña actual.</div>
                </div>

                <div class="form-group">
                    <label for="id_rol">Rol:</label>
                    <select id="id_rol" name="id_rol" required>
                        <option value="">Selecciona un rol...</option>
                        <?php
                        // Cargar roles dinámicamente y seleccionar el actual
                        $sql_roles = "SELECT id_rol, nombre FROM rol ORDER BY nombre ASC";
                        $result_roles = mysqli_query($conn, $sql_roles);
                        
                        if ($result_roles) {
                            while ($rol = mysqli_fetch_assoc($result_roles)) {
                                $selected = ($rol['id_rol'] == $usuario['id_rol']) ? 'selected' : '';
                                echo "<option value='" . $rol['id_rol'] . "' $selected>" . htmlspecialchars($rol['nombre']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Guardar Cambios</button>
                
                <a href="ver-usuarios.php" class="btn-cancelar">Cancelar y volver</a>
            </form>
        </div>
    </div>

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