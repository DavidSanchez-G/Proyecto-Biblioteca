<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

// Validar que se reciba el ID
if (!isset($_GET['id'])) {
    die("Error: No se especificó el ID del libro");
}

$id_libro = intval($_GET['id']);

// Mapeo de colecciones
$nombresColecciones = [
    'verde'    => 'INFANTIL Y 1º CICLO',
    'naranja'  => '2º Y 3º CICLO',
    'azul'     => 'ANIMALES Y NATURALEZA',
    'rojo'     => 'VALORES',
    'rosa'     => 'EMOCIONES',
    'violeta'  => 'IGUALDAD',
    'amarillo' => 'INGLÉS',
    'marron'   => 'COLECCIONES',
    'blanco'   => 'CÓMICS',
    'negro'    => 'MÚSICA'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    if (isset($_POST['titulo']) && trim($_POST['titulo']) !== '') {
        $updates[] = "titulo='" . mysqli_real_escape_string($conn, $_POST['titulo']) . "'";
    }
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $nombre = $_FILES['portada']['name'];
        $temporal = $_FILES['portada']['tmp_name'];
        $carpeta = "imagenes/";
        $ruta = $carpeta . uniqid() . "_" . basename($nombre);
        if (move_uploaded_file($temporal,"../". $ruta)) {
            $updates[] = "portada='" . mysqli_real_escape_string($conn, $ruta) . "'";
        }
    }
    if (isset($_POST['autor']) && trim($_POST['autor']) !== '') {
        $updates[] = "autor='" . mysqli_real_escape_string($conn, $_POST['autor']) . "'";
    }
    if (isset($_POST['isbn']) && trim($_POST['isbn']) !== '') {
        $updates[] = "isbn='" . mysqli_real_escape_string($conn, $_POST['isbn']) . "'";
    }
    if (isset($_POST['ubicacion_por_colores']) && trim($_POST['ubicacion_por_colores']) !== '') {
        $updates[] = "ubicacion_por_colores='" . mysqli_real_escape_string($conn, $_POST['ubicacion_por_colores']) . "'";
    }
    if (isset($_POST['estado_de_actividad'])) {
        if ($_POST['estado_de_actividad'] == '0') {
            $estadoActividad = 0;
        } else {
            $estadoActividad = 1;
        }
            $updates[] = "estado_de_actividad='$estadoActividad'";
    
        $updates[] = "estado_de_actividad=" . $_POST['estado_de_actividad'];
    }
    
    if (!empty($updates)) {
        $sql_update = "UPDATE libro SET " . implode(', ', $updates) . " WHERE id_libro=$id_libro";
        if (!mysqli_query($conn, $sql_update)) {
            die("Error al actualizar: " . mysqli_error($conn));
        } 
    } 
}

// Consultar la base de datos para mostrar info actual
$sql = "SELECT * FROM libro WHERE id_libro = $id_libro";
$resultado = mysqli_query($conn, $sql);
$libro = mysqli_fetch_array($resultado);

if (!$libro) {
    die("Error: Libro no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar libro</title>
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
        <div class="contenedor-formulario">
            <section class="form-caja">
                <h2>Editar Libro: <?php echo htmlspecialchars($libro['titulo']); ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Título:</label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Autor/Editorial:</label>
                        <input type="text" name="autor" value="<?php echo htmlspecialchars($libro['autor']); ?>">
                    </div>
                    <div class="form-group">
                        <label>ISBN:</label>
                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($libro['isbn']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Colección:</label>
                        <select name="ubicacion_por_colores" required>
                            <?php 
                            foreach ($nombresColecciones as $valor => $nombre) {
                                $selected = ($libro['ubicacion_por_colores'] == $valor) ? 'selected' : '';
                                echo "<option value=\"$valor\" $selected>$nombre</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado:</label>
                        <select name="estado_de_actividad">
                            <option value="1"> Disponible</option>
                            <option value="0" >No disponible</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cambiar Portada:</label>
                        <input type="file" name="portada" accept="image/*">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-submit">Actualizar</button>
                        <a href="verlibro.php?id=<?php echo $id_libro; ?>" class="btn-submit" style="background-color: #666; text-decoration: none; text-align: center;">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
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