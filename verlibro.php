<?php
session_start();
require 'conexion.php';

// Validar que se reciba el ID
if (!isset($_GET['id'])) {
    die("Error: No se especificó el ID del libro");
}

$id_libro = intval($_GET['id']);

// Consultar la base de datos
$sql = "SELECT * FROM libro WHERE id_libro = $id_libro";
$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conn));
}

$libro = mysqli_fetch_array($resultado);

if (!$libro) {
    die("Error: Libro no encontrado");
}

// --- TRADUCCIÓN DE COLOR A NOMBRE DE COLECCIÓN ---
$nombresColecciones = [
    'verde'    => 'INFANTIL Y 1º CICLO',
    'naranja'  => '2º Y 3º CICLO',
    'azul'     => 'ANIMALES Y NATURALEZA',
    'rojo'     => 'VALORES',
    'rosa'     => 'EMOCIONES',
    'violeta'  => 'IGUALDAD',
    'amarillo' => 'INGLÉS',
    'marron'   => 'COLECCIONES',
    'marrón'   => 'COLECCIONES',
    'blanco'   => 'CÓMICS',
    'negro'    => 'MÚSICA'
];

$color = strtolower($libro['ubicacion_por_colores']);
// Si existe en la lista usamos el nombre, si no, el original
$textoColeccion = isset($nombresColecciones[$color]) ? $nombresColecciones[$color] : $libro['ubicacion_por_colores'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($libro['titulo']); ?> - Biblioteca</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav>
        <ul>
            <li class="botones-nav"><a href="iniciosesion.php"><button>Iniciar sesion</button></a></li> 
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
        <articule class="detalle-libro">
            <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
            
            <section class="info-libro">
                <h1><?php echo htmlspecialchars($libro['titulo']); ?></h1>
                
                <p>
                    <strong>Autor:</strong>
                    <?php echo htmlspecialchars($libro['autor']); ?>
                </p>
                
                <p>
                    <strong>ISBN:</strong>
                    <?php echo htmlspecialchars($libro['isbn']); ?>
                </p>
                
                <p>
                    <strong>Colección:</strong>
                    <span class="badge <?php echo $color; ?>">
                        <?php echo htmlspecialchars($textoColeccion); ?>
                    </span>
                </p>
                
                <p>
                    <strong>Estado:</strong>
                    <?php 
                        $claseEstado = ($libro['estado_de_actividad'] == 0) ? 'activo' : 'inactivo'; // Asumiendo 0=Disponible
                        $textoEstado = ($libro['estado_de_actividad'] == 0) ? 'Disponible' : 'No disponible';
                    ?>
                    <span class="badge <?php echo $claseEstado; ?>">
                        <?php echo $textoEstado; ?>
                    </span>
                </p>
                
                <section class="botones">
                    <a href="index.php"><button>Volver al índice</button></a>
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