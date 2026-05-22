<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

$error = "";

// 1. LÓGICA PARA GUARDAR CAMBIOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prestamo = intval($_POST['id_prestamo']);
    $id_alumnado = intval($_POST['id_alumnado']);
    $id_libro = intval($_POST['id_libro']);
    $fecha_salida = $_POST['fecha_de_salida'];
    $estado = $_POST['estado_del_prestamo'];
    
    // Manejo especial para la fecha de devolución:
    // Si el campo está vacío, enviamos NULL a la base de datos
    if (empty($_POST['fecha_de_devolucion'])) {
        $fecha_devolucion_sql = "NULL";
    } else {
        $fecha_devolucion_sql = "'" . $_POST['fecha_de_devolucion'] . "'";
    }

    $sql_update = "UPDATE prestamo 
                   SET id_alumnado = $id_alumnado,
                       id_libro = $id_libro,
                       fecha_de_salida = '$fecha_salida',
                       fecha_de_devolucion = $fecha_devolucion_sql,
                       estado_del_prestamo = '$estado'
                   WHERE id_prestamo = $id_prestamo";

    if (mysqli_query($conn, $sql_update)) {
        header("Location: ver-prestamos.php?mensaje=Préstamo actualizado correctamente");
        exit();
    } else {
        $error = "Error al actualizar: " . mysqli_error($conn);
    }
}

// 2. LÓGICA PARA CARGAR DATOS (GET)
if (isset($_GET['id'])) {
    $id_prestamo = intval($_GET['id']);
    
    // Obtenemos los datos del préstamo
    $sql = "SELECT * FROM prestamo WHERE id_prestamo = $id_prestamo";
    $resultado = mysqli_query($conn, $sql);
    $prestamo = mysqli_fetch_assoc($resultado);

    if (!$prestamo) {
        header("Location: ver-prestamos.php?mensaje=El préstamo no existe");
        exit();
    }
} else {
    // Si no hay ID y no es POST, volvemos al listado
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ver-prestamos.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Préstamo</title>
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
                <form action="ver-prestamos.php" method="GET">
                    <input type="text" name="busqueda" placeholder="Buscar préstamo..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="contenedor-formulario" style="margin-top: 150px;">
        <div class="form-caja">
            <h2 style="text-align: center; color: #602b06; margin-top: 0;">Editar Préstamo</h2>
            
            <?php if (!empty($error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="editar_prestamo.php" method="POST">
                <input type="hidden" name="id_prestamo" value="<?php echo $prestamo['id_prestamo']; ?>">

                <div class="form-group">
                    <label for="id_alumnado">Alumno:</label>
                    <select id="id_alumnado" name="id_alumnado" required>
                        <?php
                        $sql_alum = "SELECT id_alumnado, nombre, apellidos FROM alumnado ORDER BY nombre ASC";
                        $res_alum = mysqli_query($conn, $sql_alum);
                        while ($row = mysqli_fetch_assoc($res_alum)) {
                            $selected = ($row['id_alumnado'] == $prestamo['id_alumnado']) ? 'selected' : '';
                            echo "<option value='{$row['id_alumnado']}' $selected>" . htmlspecialchars($row['nombre'] . " " . $row['apellidos']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_libro">Libro:</label>
                    <select id="id_libro" name="id_libro" required>
                        <?php
                        $sql_lib = "SELECT id_libro, titulo FROM libro ORDER BY titulo ASC";
                        $res_lib = mysqli_query($conn, $sql_lib);
                        while ($row = mysqli_fetch_assoc($res_lib)) {
                            $selected = ($row['id_libro'] == $prestamo['id_libro']) ? 'selected' : '';
                            // Cortamos el título si es muy largo para que quepa en el select
                            $titulo_corto = strlen($row['titulo']) > 50 ? substr($row['titulo'], 0, 50) . "..." : $row['titulo'];
                            echo "<option value='{$row['id_libro']}' $selected>" . htmlspecialchars($titulo_corto) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_de_salida">Fecha de Salida:</label>
                    <input type="date" id="fecha_de_salida" name="fecha_de_salida" value="<?php echo $prestamo['fecha_de_salida']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="fecha_de_devolucion">Fecha de Devolución:</label>
                    <input type="date" id="fecha_de_devolucion" name="fecha_de_devolucion" value="<?php echo $prestamo['fecha_de_devolucion']; ?>">
                    <small style="color: #666;">Dejar vacío si aún no se ha devuelto.</small>
                </div>

                <div class="form-group">
                    <label for="estado_del_prestamo">Estado:</label>
                    <select id="estado_del_prestamo" name="estado_del_prestamo" required>
                        <option value="Prestado" <?php if($prestamo['estado_del_prestamo'] == 'Prestado') echo 'selected'; ?>>Prestado</option>
                        <option value="Devuelto" <?php if($prestamo['estado_del_prestamo'] == 'Devuelto') echo 'selected'; ?>>Devuelto</option>
                        <option value="Atrasado" <?php if($prestamo['estado_del_prestamo'] == 'Atrasado') echo 'selected'; ?>>Atrasado</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Guardar Cambios</button>
                <a href="ver-prestamos.php" class="btn-cancelar">Cancelar</a>
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