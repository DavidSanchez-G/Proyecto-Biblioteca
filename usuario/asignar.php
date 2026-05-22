<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

if (isset($_GET['id'] )) {
    $id_libro = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $id_libro = intval($_POST['id']);
} else {
    header("location: catalogo.php");
    exit;
}


$sql = "SELECT * FROM libro WHERE id_libro = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_libro);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$libro = mysqli_fetch_assoc($result);
if (!$libro) {
    header("location: catalogo.php");
    exit;
}
// Procesar el formulario de préstamo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    $id_alumnado = $_POST['id_alumnado'];
    //comprobamos que el alumno no este sancionado
    $sql_alumno = "SELECT * FROM alumnado WHERE id_alumnado = ?";
    $stmt_alumno = mysqli_prepare($conn, $sql_alumno);
    mysqli_stmt_bind_param($stmt_alumno, "i", $id_alumnado);
    mysqli_stmt_execute($stmt_alumno);
    $result_alumno = mysqli_stmt_get_result($stmt_alumno);
    $alumno = mysqli_fetch_assoc($result_alumno);

    if (!$alumno) {
        header("location: asignar.php?id=$id_libro&mensaje=Alumno no encontrado");
        exit;
    }

    if (!empty($alumno['estado_de_sancion'])) {
        header("location: asignar.php?id=$id_libro&mensaje=El alumno está sancionado");
        exit;
    } else {
        if ($libro['estado_de_actividad'] == '1') {
            header("location: asignar.php?id=$id_libro&mensaje=Libro no disponible");
            exit;
        } else {
            $id_usuario = $_SESSION['usuario'];
            $fecha_salida = date('Y-m-d');
            $fecha_devolucion = date('Y-m-d', strtotime('+15 days'));
            $estado = 'Prestado';

            $sql_insert = "INSERT INTO prestamo (id_alumnado, id_libro, id_usuario, fecha_de_salida, fecha_de_devolucion, estado_del_prestamo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iiisss", $id_alumnado, $id_libro, $id_usuario, $fecha_salida, $fecha_devolucion, $estado);
            if (mysqli_stmt_execute($stmt_insert)) {
                $sql_update = "UPDATE libro SET estado_de_actividad = '1' WHERE id_libro = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "i", $id_libro);
                mysqli_stmt_execute($stmt_update);
                header("location: asignar.php?id=$id_libro&mensaje=Préstamo realizado con éxito");
                exit;
            } else {
                header("location: asignar.php?id=$id_libro&mensaje=Error al procesar el préstamo");
                exit;
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
    <title><?php echo htmlspecialchars($libro['titulo']); ?> - Biblioteca</title>
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
        <articule class="detalle-libro">
            <img src="../<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
            
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
                    <span class="badge <?php echo strtolower($libro['ubicacion_por_colores']); ?>">
                        <?php echo htmlspecialchars($libro['ubicacion_por_colores']); ?>
                    </span>
                </p>

     <p class="asignar-libro">
    <strong>Asignar Libro a Alumno</strong>

    <div class="buscador-alumno">
        <input type="text" id="buscar-alumno"
               placeholder="Buscar alumno por nombre"
               autocomplete="off">
        <div id="dropdown-alumnos"></div>
    </div>
</p>

                
                <p>
                    <strong>Estado:</strong>
                    <span class="badge <?php echo ($libro['estado_de_actividad'] == 0) ? 'disponible' : 'no-disponible'; ?>">
                        <?php echo ($libro['estado_de_actividad'] == 0) ? 'Disponible' : 'No disponible'; ?>
                    </span>
                </p>
                <?php if (isset($_GET['mensaje'])): ?>
                    <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
                        <?php echo htmlspecialchars($_GET['mensaje']); ?>
                    </div>
                <?php endif; ?>
                <section class="botones">
                    <a href="catalogo.php"><button>Volver al índice</button></a>
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

    <div id="modal-confirmacion" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:5px;">
            <p>¿Estás seguro de asignar el libro "<?php echo htmlspecialchars($libro['titulo']); ?>" a <span id="alumno-nombre"></span>?</p>
            <form method="POST" action="asignar.php">
                <input type="hidden" name="id" value="<?php echo $id_libro; ?>">
                <input type="hidden" name="id_alumnado" id="id_alumnado_hidden">
                <button type="submit" name="confirmar" id="confirmar-btn">Confirmar</button>
                <button type="button" onclick="cerrarModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        const input = document.getElementById('buscar-alumno');
        const dropdown = document.getElementById('dropdown-alumnos');
        let selectedAlumno = null;
        const id_libro = <?php echo json_encode($id_libro); ?>;

        input.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }
            fetch(`buscar_alumnos_ajax.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(alumno => {
                            const item = document.createElement('div');
                            item.textContent = `${alumno.nombre} ${alumno.apellidos} (${alumno.codigo_de_carnet})`;
                            item.style.padding = '10px';
                            item.style.cursor = 'pointer';
                            item.addEventListener('click', () => seleccionarAlumno(alumno));
                            dropdown.appendChild(item);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                });
        });

        function seleccionarAlumno(alumno) {
            selectedAlumno = alumno;
            input.value = `${alumno.nombre} ${alumno.apellidos} (${alumno.codigo_de_carnet})`;
            dropdown.style.display = 'none';
            document.getElementById('alumno-nombre').textContent = `${alumno.nombre} ${alumno.apellidos}`;
            document.getElementById('id_alumnado_hidden').value = alumno.id_alumnado;
            document.getElementById('modal-confirmacion').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modal-confirmacion').style.display = 'none';
        }

        document.getElementById('confirmar-btn').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('confirmar', '1');
            formData.append('id_alumnado', document.getElementById('id_alumnado_hidden').value);
            formData.append('id', id_libro);
            fetch('asignar.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    alert('Error al procesar el préstamo');
                }
            }).catch(error => {
                console.error('Error:', error);
            });
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>