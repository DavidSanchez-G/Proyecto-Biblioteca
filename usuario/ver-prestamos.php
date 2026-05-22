<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

// --- 1. ACTUALIZACIÓN AUTOMÁTICA DE ATRASADOS ---
$sql_update = "UPDATE prestamo 
               SET estado_del_prestamo = 'Atrasado' 
               WHERE fecha_de_devolucion < CURDATE() 
               AND estado_del_prestamo = 'Prestado'";
mysqli_query($conn, $sql_update);

// --- 2. RECUPERAR BÚSQUEDA ---
$busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($conn, $_GET['busqueda']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Préstamos - Profesor</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Estilos de estado */
        .badge-prestado { background-color: #ffca0c; color: #602b06; }
        .badge-devuelto { background-color: #BCFC88; color: #155724; }
        .badge-atrasado { background-color: #ffcccc; color: #cc0000; font-weight: bold; }
        
        .info-busqueda {
            text-align: center;
            margin-top: 140px;
            margin-bottom: -15px;
            color: #602b06;
            font-size: 1.1rem;
        }

        /* Estilo para los datos secundarios (ISBN y Carnet) */
        .sub-info {
            display: block;
            font-size: 0.75rem;
            color: #666;
            font-weight: normal;
            margin-top: 2px;
        }
        
        .estado-badge { padding: 4px 8px; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li class="botones-nav">
                <section class="menu-desplegable">
                    <button>☰ Menú</button>
                    <article class="menu-contenido">
                        <a href="catalogo.php">Ver Catalogo</a>
                        <a href="ver-prestamos.php"> Ver Préstamos</a>
                        <a href="ver-alumno.php"> Ver Alumnos</a>
                        <a href="anadir_alumno.php"> Añadir alumno</a>
                        <a href="perfil.php" class="menucuenta"> Perfil</a>
                        <a href="../cerrar_sesion.php" class="cerrarsesion"> Cerrar sesion</a>
                    </article>
                </section>
            </li> 
            <li class="botones-nav"><h1>Biblioteca<br>Andres Manjon</h1></li>
            <li class="botones-nav">
                <form action="ver-prestamos.php" method="GET">
                    <input type="text" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Alumno, Título, ISBN o Carnet..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <main style="width: 100vw; margin: 0; padding: 0; min-height: 80vh;">
    <?php if(!empty($busqueda)): ?>
        <div class="info-busqueda">
            Resultados para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
            <a href="ver-prestamos.php" style="font-size: 0.9rem; margin-left: 10px; color: #2196F3;">(Ver todos)</a>
        </div>
        <section class="filtros-container" style="margin-top: 20px; position: relative; z-index: 100;">
    <?php else: ?>
        <section class="filtros-container" style="margin-top: 130px; position: relative; z-index: 100;">
    <?php endif; ?>

        <article class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Estado</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="estado_prestamo" value="Prestado" onchange="aplicarFiltros()"> Prestado</label>
                <label><input type="checkbox" name="estado_prestamo" value="Devuelto" onchange="aplicarFiltros()"> Devuelto</label>
                <label><input type="checkbox" name="estado_prestamo" value="Atrasado" onchange="aplicarFiltros()"> Atrasado</label>
            </div>
        </article>
    </section>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <article class="vista-lista" style="display: block; margin-top: 20px;">
        <table>
            <thead>
                <tr>
                    <th>Alumno / Carnet</th>
                    <th>Libro / ISBN</th>
                    <th>Fecha Salida</th>
                    <th>Fecha Devolución</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    <?php
        // --- 3. CONSULTA SQL DINÁMICA MEJORADA ---
        $sql = "SELECT 
                    p.id_prestamo, 
                    a.nombre, 
                    a.apellidos, 
                    a.codigo_de_carnet,
                    l.titulo, 
                    l.isbn,
                    p.fecha_de_salida, 
                    p.fecha_de_devolucion, 
                    p.estado_del_prestamo 
                FROM prestamo p
                LEFT JOIN alumnado a ON p.id_alumnado = a.id_alumnado
                LEFT JOIN libro l ON p.id_libro = l.id_libro";
        
        if (!empty($busqueda)) {
            $sql .= " WHERE a.nombre LIKE '%$busqueda%' 
                      OR a.apellidos LIKE '%$busqueda%' 
                      OR l.titulo LIKE '%$busqueda%'
                      OR l.isbn LIKE '%$busqueda%'
                      OR a.codigo_de_carnet LIKE '%$busqueda%'";
        }

        $sql .= " ORDER BY p.id_prestamo DESC"; 
        
        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            echo "<tr><td colspan='6' style='text-align: center; color: red;'>Error en la consulta.</td></tr>";
        } else {
            if (mysqli_num_rows($result) == 0) {
                echo "<tr><td colspan='6' style='text-align: center; color: #666;'>No hay resultados</td></tr>";
            } else {
                while ($prestamo = mysqli_fetch_assoc($result)) {
                    $estado = $prestamo['estado_del_prestamo'];
                    $estadoClass = 'badge-prestado'; 
                    if ($estado === 'Devuelto') $estadoClass = 'badge-devuelto';
                    elseif ($estado === 'Atrasado') $estadoClass = 'badge-atrasado';
                    
                    $fechaDev = $prestamo['fecha_de_devolucion'] ? $prestamo['fecha_de_devolucion'] : '-';
    ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($prestamo['nombre'] . " " . $prestamo['apellidos']); ?></strong>
                        <span class="sub-info">ID: <?php echo htmlspecialchars($prestamo['codigo_de_carnet']); ?></span>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($prestamo['titulo']); ?>
                        <span class="sub-info">ISBN: <?php echo htmlspecialchars($prestamo['isbn']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($prestamo['fecha_de_salida']); ?></td>
                    <td><?php echo htmlspecialchars($fechaDev); ?></td>
                    <td>
                        <span class="estado-badge <?php echo $estadoClass; ?>">
                            <?php echo htmlspecialchars($estado); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($estado == 'Prestado' || $estado == 'Atrasado'): ?>
                            <a href="devolver_libro.php?id=<?php echo $prestamo['id_prestamo']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #4CAF50; border-radius: 4px; font-size: 0.85rem;">Devolver</a>
                        <?php endif; ?>
                        
                        <a href="editar_prestamo.php?id=<?php echo $prestamo['id_prestamo']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #2196F3; border-radius: 4px; font-size: 0.85rem;">Editar</a>
                    </td>
                </tr>
    <?php
                }
            }
        }
    ?>
            </tbody>
        </table>
    </article>
    </main>

    <footer>
        <section id="footer-cabecera">
            <a href="https://ceipandresmanjon.catedu.es"><h2>CEIP Andres Manjon</h2></a>
        </section>
        <section class="footer-section">
            <p>C. de las Delicias, 90, 50017 Zaragoza.</p>
        </section>
        <section class="footer-section">
            <p>Tlf: +34 976 331 728</p>
        </section>
    </footer>

    <script>
        function toggleFiltro(button) {
            const contenido = button.nextElementSibling;
            contenido.style.display = (contenido.style.display === 'none' || contenido.style.display === '') ? 'block' : 'none';
        }
        
        function aplicarFiltros() {
            const estadoSeleccionado = Array.from(document.querySelectorAll('input[name="estado_prestamo"]:checked')).map(el => el.value);
            document.querySelectorAll('.vista-lista tbody tr').forEach(fila => {
                const estadoSpan = fila.querySelector('td:nth-child(5) span');
                let mostrar = (estadoSeleccionado.length === 0) || (estadoSpan && estadoSeleccionado.includes(estadoSpan.textContent.trim()));
                fila.style.display = mostrar ? '' : 'none';
            });
        }
    </script>
</body>
</html>