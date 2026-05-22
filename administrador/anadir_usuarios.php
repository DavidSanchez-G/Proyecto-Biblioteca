<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

$mensaje = "";
$error = "";

// PROCESAR EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar datos de entrada
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_raw = $_POST['contrasenia'];
    $id_rol = intval($_POST['id_rol']);

    // 1. Verificar si el usuario ya existe
    $check_sql = "SELECT id_usuario FROM usuario WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "El nombre de usuario '$username' ya está en uso.";
    } else {
        // 2. Encriptar contraseña (Nunca guardar en texto plano)
        // Usamos PASSWORD_DEFAULT que es el estándar actual seguro de PHP
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

        // 3. Insertar usuario
        $sql = "INSERT INTO usuario (nombre, username, contrasenia, codigo_de_carnet, id_rol) 
                VALUES ('$nombre', '$username', '$password_hash', '$codigo_carnet', $id_rol)";
        if (mysqli_query($conn, $sql)) {
            // Actualizar el codigo_de_carnet 
            // Generar codigo_de_carnet: rol + id formateado, para ello buscamos el rol del usuario
            $id_usuario = mysqli_insert_id($conn);
            $sql_rol = "SELECT * FROM rol WHERE id_rol = $id_rol";
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
            // Redirigir a la lista con mensaje de éxito
            header("Location: ver-usuarios.php?mensaje=Usuario creado correctamente");
            exit();
        } else {
            $error = "Error al crear usuario: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Usuario</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        
    </style>
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
            <h2 style="text-align: center; color: #602b06; margin-top: 0;">Registrar Nuevo Usuario</h2>
            
            <?php if (!empty($error)): ?>
                <div class="mensaje-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="anadir_usuarios.php" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                </div>

                <div class="form-group">
                    <label for="username">Usuario (Login):</label>
                    <input type="text" id="username" name="username" required placeholder="Ej: jperez">
                </div>

                <div class="form-group">
                    <label for="contrasenia">Contraseña:</label>
                    <input type="password" id="contrasenia" name="contrasenia" required placeholder="******">
                </div>


                <div class="form-group">
                    <label for="id_rol">Rol:</label>
                    <select id="id_rol" name="id_rol" required>
                        <option value="">Selecciona un rol...</option>
                        <?php
                        // Cargar roles dinámicamente desde la BD
                        $sql_roles = "SELECT id_rol, nombre FROM rol ORDER BY nombre ASC";
                        $result_roles = mysqli_query($conn, $sql_roles);
                        
                        if ($result_roles) {
                            while ($rol = mysqli_fetch_assoc($result_roles)) {
                                echo "<option value='" . $rol['id_rol'] . "'>" . htmlspecialchars($rol['nombre']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Guardar Usuario</button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="ver-usuarios.php" style="color: #666; text-decoration: none;">Cancelar y volver</a>
                </div>
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