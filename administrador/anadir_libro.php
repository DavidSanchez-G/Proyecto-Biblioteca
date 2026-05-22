    <?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

$error = ""; // Inicializamos variable de error
$exito = ""; // Inicializamos variable de éxito

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    
    // Recoger datos
    if (isset($_POST['titulo']) && trim($_POST['titulo']) !== '') {
        $updates[] = "'" . mysqli_real_escape_string($conn, $_POST['titulo']) . "'";
    }
    
    // Manejo de la Imagen
    $target_dir = "../imagenes/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    if (!is_writable($target_dir)) {
        $error = "El directorio de imágenes no tiene permisos de escritura.";
    } elseif (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $nombre = $_FILES['portada']['name'];
        $temporal = $_FILES['portada']['tmp_name'];
        $carpeta = "imagenes/";
        $ruta = $carpeta . uniqid() . "_" . basename($nombre);
        
        if (move_uploaded_file($temporal, "../" . $ruta)) {
            $updates[] = "'" . mysqli_real_escape_string($conn, $ruta) . "'";
        } else {
            $error = "Error al mover el archivo de imagen.";
        }
    } elseif (isset($_FILES['portada']) && $_FILES['portada']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Error en la subida del archivo de imagen:(comprueba tamaño) " . $_FILES['portada']['error'];
    } else {
        // Si no suben foto, ponemos la predefinida (opcional, si quieres que tenga una por defecto)
        // $updates[] = "'imagenes/predefinido.png'";
        // Si prefieres dejarlo NULL o no incluirlo, déjalo como está tu código original:
        // (Tu código original solo añade la imagen si se sube una, lo mantengo así).
    }

    if (isset($_POST['autor']) && trim($_POST['autor']) !== '') {
        $updates[] = "'" . mysqli_real_escape_string($conn, $_POST['autor']) . "'";
    }
    if (isset($_POST['isbn']) && trim($_POST['isbn']) !== '') {
        $updates[] = "'" . mysqli_real_escape_string($conn, $_POST['isbn']) . "'";
    }
    if (isset($_POST['ubicacion_por_colores']) && trim($_POST['ubicacion_por_colores']) !== '') {
        $updates[] = "'" . mysqli_real_escape_string($conn, $_POST['ubicacion_por_colores']) . "'";
    }
    if (isset($_POST['estado_de_actividad'])) {
        $updates[] = "'" . mysqli_real_escape_string($conn, $_POST['estado_de_actividad']) . "'";
    }

    // Insertar en Base de Datos
    if (!empty($updates) && empty($error)) {
        // Nota: Asegúrate de que los campos en el INSERT coincidan con el orden de $updates.
        // Tu código original asume que siempre se llenan todos en orden. 
        // Si el usuario no sube foto, $updates tendrá un valor menos y fallará el SQL.
        // Lo ideal es especificar las columnas dinámicamente, pero para arreglar el error del ISBN:
        
        // Asumiendo que SIEMPRE llenas estos campos (según tu form required), menos la foto:
        // Si la foto es opcional, tu array $updates puede desordenarse. 
        // He ajustado el SQL para que sea más robusto abajo:
        
        $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
        $autor = mysqli_real_escape_string($conn, $_POST['autor']);
        $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
        $ubicacion = mysqli_real_escape_string($conn, $_POST['ubicacion_por_colores']);
        $estado = mysqli_real_escape_string($conn, $_POST['estado_de_actividad']);
        
        // Si hay ruta de imagen, la usamos, si no, usamos la predefinida
        $ruta_final = isset($ruta) ? $ruta : 'imagenes/predefinido.png'; 
        $sql_insert = "INSERT INTO libro (titulo, autor, isbn, ubicacion_por_colores, estado_de_actividad, portada) 
                        VALUES ('$titulo', '$autor', '$isbn', '$ubicacion', '$estado', '$ruta_final')";
        for ($i = 1; $i <= intval($_POST['cantidad']); $i++) {
            if (mysqli_query($conn, $sql_insert)) {
                    $exito = "Libro añadido correctamente.";
            } else {
                    $error = "Error al añadir el libro: " . mysqli_error($conn);
            }  
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
    <title>Añadir libros</title>
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
                <form action="buscar.php" method="GET">
                    <input type="text" name="buscar" placeholder="Buscar libro..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <main class="detalle-libro-main">
        <articule class="contenedor-formulario">
            <section class="form-caja">
                    
                    <?php if (!empty($exito)) { echo "<h4 style='color:green; text-align:center;'>".htmlspecialchars($exito)."</h4>"; } ?>
                    
                    <?php if (!empty($error)) { echo "<h4 style='color:red; text-align:center;'>".htmlspecialchars($error)."</h4>"; } ?>

                    <h2>Añadir Libro</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                        <label>Título: <input type="text" name="titulo" required></label>
                        </div>

                        <div class="form-group">
                        <label>Autor: <input type="text" name="autor" required></label>
                        </div>

                        <div class="form-group">
                        <label>ISBN: <input type="text" name="isbn" required></label>
                
                        </div>

                        <div class="form-group">
                        <label>Colección: 
                            <select name="ubicacion_por_colores" required>
                                <option value="verde">Infantil y 1º Ciclo</option>
                                <option value="naranja">2º y 3º Ciclo</option>
                                <option value="azul">Animales y naturaleza</option>
                                <option value="rojo">Valores</option>
                                <option value="rosa">Emociones</option>
                                <option value="violeta">Igualdad</option>
                                <option value="amarillo">Ingles</option>
                                <option value="marron">Colecciones</option>
                                <option value="blanco">Comics</option>
                                <option value="negro">Musica</option>
                            </select>
                        </label>
                        </div>

                        <div class="form-group">
                        <label>Estado: 
                            <select name="estado_de_actividad" required>
                                <option value="0">Disponible</option>
                                <option value="1">No disponible</option>
                            </select>
                        </label>
                        </div>

                        <div class="form-group">
                        <label>Portada: <input type="file" name="portada" accept="image/*" ?></label>
                        </div>

                        <div class="form-group">
                        <label>Cantidad: <input type="number" name="cantidad" min="1" placeholder="0" required></label>
                        </div>

                        <button class="btn-submit" type="submit">Añadir</button>
                    </form>
                    <a href="catalogo.php"><button class="btn-submit">Volver al índice</button></a>
                </section>

            
                
            </section>
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
