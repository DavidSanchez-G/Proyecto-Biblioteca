<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

$id_usuario = $_SESSION['usuario'];

// Consultar la base de datos
$sql = "SELECT * FROM usuario WHERE id_usuario = $id_usuario";
$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conn));
}

$usuario = mysqli_fetch_array($resultado);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($usuario['nombre']); ?></title>
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
                            <a href="ver-prestamos.php"> Ver Préstamos</a>
                        <a href="ver-alumno.php"> Ver Alumnos</a>
                            <a href="anadir_alumno.php"> Añadir alumno</a>
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
        <articule class="detalle-libro" style="width: 30%; text-align: center; justify-content: center;">
            <section class="info-libro">
                
                <h1>
                    <?php echo htmlspecialchars($_SESSION['rol']); ?>
                </h1>

                <p>
                    <strong>Profesor/a:</strong>
                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                </p>
                
                <p>
                    <strong>Nombre de usuario:</strong>
                    <?php echo htmlspecialchars($usuario['username']); ?>
                </p>
                
                <p>
                    <strong>Carnet:</strong>
                    <?php echo htmlspecialchars($usuario['codigo_de_carnet']); ?>
                    
                </p>
                
                
                
                <section class="botones">
                    <a href="cambiar_pass.php"><button>Cambiar contraseña</button></a>
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