<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

function getColorCarnet($codigo) {
    $colores = [
        'verde' => '4CAF50',
        'naranja' => 'FF9800',
        'azul' => '2196F3',
        'rojo' => 'f44336',
        'rosa' => 'E91E63',
        'violeta' => '9C27B0',
        'amarillo' => 'FDD835',
        'marrón' => '795548',
        'blanco' => 'CCCCCC',
        'negro' => '333333'
    ];
    // Convertimos a minúsculas para asegurar coincidencia
    return $colores[strtolower($codigo)] ?? 'CCCCCC';
}

// Obtener término de búsqueda desde GET
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$resultados = array();

if (!empty($buscar)) {
    // Escapar la entrada para evitar inyección SQL
    $buscar_escapado = mysqli_real_escape_string($conn, $buscar);
    
    // Búsqueda extendida: Nombre, Apellidos, Clase o Código de Carnet
    $sql = "SELECT id_alumnado, nombre, apellidos, clase, edad, codigo_de_carnet, estado_de_sancion 
            FROM alumnado 
            WHERE LOWER(nombre) LIKE LOWER('%$buscar_escapado%')
            OR LOWER(apellidos) LIKE LOWER('%$buscar_escapado%')
            OR LOWER(clase) LIKE LOWER('%$buscar_escapado%')
            OR LOWER(codigo_de_carnet) LIKE LOWER('%$buscar_escapado%')
            ORDER BY nombre ASC";
    
    $resultado = mysqli_query($conn, $sql);
    
    if ($resultado) {
        while ($alumno = mysqli_fetch_assoc($resultado)) {
            $resultados[] = $alumno;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Buscar Alumnos - Biblioteca</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Estilos rápidos para las etiquetas de estado */
        .badge-activo { background-color: #BCFC88; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .badge-sancionado { background-color: #ffcccc; color: #cc0000; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
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
                <form action="buscar_alumno.php" method="GET">
                    <input type="text" name="buscar" placeholder="Buscar alumno..." value="<?php echo htmlspecialchars($buscar); ?>" required>
                    <button type="submit">🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <?php if (!empty($buscar) && count($resultados) > 0): ?>
    <section class="filtros-container" style="margin-top: 130px; position: relative; z-index: 100;">
        <article class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Clase</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="clase" value="1º Infantil" onchange="aplicarFiltros()"> 1º Infantil</label>
                <label><input type="checkbox" name="clase" value="2º Infantil" onchange="aplicarFiltros()"> 2º Infantil</label>
                <label><input type="checkbox" name="clase" value="3º Infantil" onchange="aplicarFiltros()"> 3º Infantil</label>
                <label><input type="checkbox" name="clase" value="4º Infantil" onchange="aplicarFiltros()"> 4º Infantil</label>
                <label><input type="checkbox" name="clase" value="1º Primaria" onchange="aplicarFiltros()"> 1º Primaria</label>
                <label><input type="checkbox" name="clase" value="2º Primaria" onchange="aplicarFiltros()"> 2º Primaria</label>
                <label><input type="checkbox" name="clase" value="3º Primaria" onchange="aplicarFiltros()"> 3º Primaria</label>
                <label><input type="checkbox" name="clase" value="4º Primaria" onchange="aplicarFiltros()"> 4º Primaria</label>
                <label><input type="checkbox" name="clase" value="5º Primaria" onchange="aplicarFiltros()"> 5º Primaria</label>
                <label><input type="checkbox" name="clase" value="6º Primaria" onchange="aplicarFiltros()"> 6º Primaria</label>
            </div>
        </article>
        
        <article class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Estado</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="estado" value="0" onchange="aplicarFiltros()"> Activo</label>
                <label><input type="checkbox" name="estado" value="1" onchange="aplicarFiltros()"> Sancionado</label>
            </div>
        </article>
    </section>
    <?php else: ?>
        <div style="margin-top: 130px;"></div>
    <?php endif; ?>

    <div class="contenedor-busqueda">
        <?php if (!empty($buscar)): ?>
            <div class="resultado-info" style="text-align:center; margin: 20px;">
                <h3>Resultados para: "<?php echo htmlspecialchars($buscar); ?>"</h3>
                <p><?php echo count($resultados); ?> alumno(s) encontrado(s)</p>
                <a href="ver-alumno.php" style="color: #602b06; text-decoration: underline;">Volver a Ver Alumnos</a>
            </div>

            <?php if (count($resultados) > 0): ?>
                <article class="vista-lista" id="vistaLista" style="display: block;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Clase</th>
                                <th>Edad</th>
                                <th>Código de Carnet</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $alumno): 
                                // Preparamos variables para lógica visual
                                $estadoClass = ($alumno['estado_de_sancion'] == 0) ? 'badge-activo' : 'badge-sancionado';
                                $estadoTexto = ($alumno['estado_de_sancion'] == 0) ? 'Activo' : 'Sancionado';
                                $colorCarnet = getColorCarnet($alumno['codigo_de_carnet']);
                            ?>
                            <tr data-clase="<?php echo htmlspecialchars($alumno['clase']); ?>" 
                                data-estado="<?php echo htmlspecialchars($alumno['estado_de_sancion']); ?>">
                                
                                <td><strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($alumno['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['clase']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['edad']); ?></td>
                                <td>
                                    <span style="background-color: #<?php echo $colorCarnet; ?>; padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; display: inline-block; text-shadow: 1px 1px 1px rgba(0,0,0,0.2);">
                                        <?php echo htmlspecialchars($alumno['codigo_de_carnet']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo $estadoClass; ?>">
                                        <?php echo $estadoTexto; ?>
                                    </span>
                                </td>
                                <td style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <a href="editar.php?id=<?php echo $alumno['id_alumnado']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #4CAF50; border-radius: 4px; font-size: 0.85rem;">Editar</a>
                                    
                                    <a href="sancionar.php?id=<?php echo $alumno['id_alumnado']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #2196F3; border-radius: 4px; font-size: 0.85rem;">Sancionar</a>
                                    
                                    <a href="eliminar_alumno.php?id=<?php echo $alumno['id_alumnado']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #f44336; border-radius: 4px; font-size: 0.85rem;" onclick="return confirm('¿Estás seguro de que quieres eliminar este alumno?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            <?php else: ?>
                <div class="sin-resultados" style="text-align:center; color: #666; margin-top: 20px;">
                    <p>No se encontraron alumnos que coincidan con tu búsqueda.</p>
                    <p>Intenta buscar por nombre, apellido o curso diferente.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="sin-resultados" style="text-align:center; color: #666; margin-top: 50px;">
                <h3>Buscador de Alumnos</h3>
                <p>Ingresa el nombre, apellido o clase en la barra superior.</p>
            </div>
        <?php endif; ?>
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

    <script>
        function toggleFiltro(button) {
            const contenido = button.nextElementSibling;
            if (contenido.style.display === 'none' || contenido.style.display === '') {
                contenido.style.display = 'block';
            } else {
                contenido.style.display = 'none';
            }
        }
        
        function aplicarFiltros() {
            // Obtenemos los valores marcados
            const clasesSeleccionadas = Array.from(document.querySelectorAll('input[name="clase"]:checked')).map(el => el.value);
            const estadoSeleccionado = Array.from(document.querySelectorAll('input[name="estado"]:checked')).map(el => el.value);
            
            // Seleccionamos todas las filas
            document.querySelectorAll('.vista-lista tbody tr').forEach(fila => {
                // Leemos los datos directamente de los atributos data-* que añadimos en el PHP
                // Esto es mucho más seguro que buscar por 'td:nth-child'
                const claseAlumno = fila.getAttribute('data-clase');
                const estadoAlumno = fila.getAttribute('data-estado'); // '0' o '1'
                
                let mostrar = true;
                
                // 1. Comprobar Filtro de Clase
                if (clasesSeleccionadas.length > 0) {
                    // Si la clase del alumno NO está en la lista de seleccionadas, ocultar
                    if (!clasesSeleccionadas.includes(claseAlumno)) {
                        mostrar = false;
                    }
                }
                
                // 2. Comprobar Filtro de Estado (solo si aún se debe mostrar)
                if (mostrar && estadoSeleccionado.length > 0) {
                    if (!estadoSeleccionado.includes(estadoAlumno)) {
                        mostrar = false;
                    }
                }
                
                // Aplicar visibilidad
                fila.style.display = mostrar ? '' : 'none';
            });
        }
    </script>
</body>
</html>