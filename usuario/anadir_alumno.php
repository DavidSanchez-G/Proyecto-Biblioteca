<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellidos = htmlspecialchars($_POST['apellidos']);
    $edad = intval($_POST['edad']);
    $clase = htmlspecialchars($_POST['clase']);

    // Insertar el alumno
    $sql = "INSERT INTO alumnado (nombre, apellidos, edad, clase) VALUES ('$nombre', '$apellidos', $edad, '$clase')";
    if (mysqli_query($conn, $sql)) {
        $id_alumnado = mysqli_insert_id($conn);
        // Generar codigo_de_carnet: clase + id formateado
        switch ($clase) {
            case "1º Infantil":
                $codigo_clase = "1IN";
                break;
            case "2º Infantil":
                $codigo_clase = "2IN";
                break;
            case "3º Infantil":
                $codigo_clase = "3IN";
                break;
            case "4º Infantil":
                $codigo_clase = "4IN";
                break;
            case "1º Primaria":
                $codigo_clase = "1PR";
                break;
            case "2º Primaria":
                $codigo_clase = "2PR";
                break;
            case "3º Primaria":
                $codigo_clase = "3PR";
                break;
            case "4º Primaria":
                $codigo_clase = "4PR";
                break;
            case "5º Primaria":
                $codigo_clase = "5PR";
                break;
            case "6º Primaria":
                $codigo_clase = "6PR";
                break;
        }
        $codigo_de_carnet = $codigo_clase . '-' . str_pad($id_alumnado, 5, '0', STR_PAD_LEFT);
        // Actualizar el codigo_de_carnet
        $update_sql = "UPDATE alumnado SET codigo_de_carnet = '$codigo_de_carnet' WHERE id_alumnado = $id_alumnado";
        mysqli_query($conn, $update_sql);
        $exito = "Alumno añadido correctamente con código de carnet: $codigo_de_carnet";
    } else {
        $error = "Error al añadir alumno: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir alumnado</title>
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
                <form action="ver-alumno.php">
                    <input type="text" name="buscar" placeholder="Buscar alumno..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <main class="detalle-libro-main">
        <div class="contenedor-formulario">
            
            <section class="form-caja">
                    <?php if (isset($exito)) { echo "<h4>" . htmlspecialchars($exito) . "</h4>"; } ?>
                    <?php if (isset($error)) { echo "<h4 style='color: red;'>" . htmlspecialchars($error) . "</h4>"; } ?>
                    <h2>Añadir Alumnado</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                        <label>Nombre: <input type="text" name="nombre" required></label>
                        </div>
                        <div class="form-group">
                        <label>Apellidos: <input type="text" name="apellidos" required></label>
                        </div>
                        <div class="form-group">
                        <label>Edad: <input type="number" name="edad" required></label>
                        </div>
                        <div class="form-group">
                        <label>Curso: 
                            <select name="clase" required>
                                <option value="1º Infantil">1º Infantil</option>
                                <option value="2º Infantil">2º Infantil</option>
                                <option value="3º Infantil">3º Infantil</option>
                                <option value="4º Infantil">4º Infantil</option>
                                <option value="1º Primaria">1º Primaria</option>
                                <option value="2º Primaria">2º Primaria</option>
                                <option value="3º Primaria">3º Primaria</option>
                                <option value="4º Primaria">4º Primaria</option>
                                <option value="5º Primaria">5º Primaria</option>
                                <option value="6º Primaria">6º Primaria</option>
                            </select>
                        </label>
                        </div>
                    
                        <button class="btn-submit" type="submit">Añadir</button>
                        
                            
                        </form>
                        <a href="catalogo.php"><button class="btn-submit">Volver al índice</button></a> 
                    </section>

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