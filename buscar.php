<?php
session_start();
require 'conexion.php';

// Obtener término de búsqueda desde GET
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$resultados = array();

if (!empty($buscar)) {
    // Escapar la entrada para evitar inyección SQL
    $buscar_escapado = mysqli_real_escape_string($conn, $buscar);
    
    // Búsqueda sin importar mayúsculas/minúsculas
    // Busca en título, ISBN y autor
    $sql = "SELECT * FROM libro 
            WHERE LOWER(titulo) LIKE LOWER('%$buscar_escapado%')
            OR LOWER(isbn) LIKE LOWER('%$buscar_escapado%')
            OR LOWER(autor) LIKE LOWER('%$buscar_escapado%')
            ORDER BY titulo ASC";
    
    $resultado = mysqli_query($conn, $sql);
    
    if ($resultado) {
        while ($libro = mysqli_fetch_array($resultado)) {
            $resultados[] = $libro;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Búsqueda - Biblioteca</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>

    </style>
</head>
<body>
    <nav>
        <ul>
            <li class="botones-nav"><a href="iniciosesion.php"><button>Iniciar sesion</button></a></li> 
            <li class="botones-nav"><h1>Biblioteca<br>Andres Manjon</h1></li>
            <li class="botones-nav">
                <form action="buscar.php" method="GET">
                    <input type="text" name="buscar" placeholder="Buscar libro..." value="<?php echo htmlspecialchars($buscar); ?>" required>
                    <button type="submit">🔍</button>
                </form>
            </li>
            
        </ul>
    </nav>

    <section class="vista-selector">
        <button id="btnToggleVista" onclick="toggleVista()"> Cambiar Vista</button>
    </section>

    <section class="filtros-container">
        <div class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Colección</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="coleccion" value="INFANTIL Y 1o CICLO" onchange="aplicarFiltros()"> INFANTIL Y 1o CICLO</label>
                <label><input type="checkbox" name="coleccion" value="2o 3o CICLO" onchange="aplicarFiltros()"> 2o 3o CICLO</label>
                <label><input type="checkbox" name="coleccion" value="ANIMALES Y NATURALEZA" onchange="aplicarFiltros()"> ANIMALES Y NATURALEZA</label>
                <label><input type="checkbox" name="coleccion" value="VALORES" onchange="aplicarFiltros()"> VALORES</label>
                <label><input type="checkbox" name="coleccion" value="EMOCIONES" onchange="aplicarFiltros()"> EMOCIONES</label>
                <label><input type="checkbox" name="coleccion" value="IGUALDAD" onchange="aplicarFiltros()"> IGUALDAD</label>
                <label><input type="checkbox" name="coleccion" value="INGLÉS" onchange="aplicarFiltros()"> INGLÉS</label>
                <label><input type="checkbox" name="coleccion" value="COLECCIONES" onchange="aplicarFiltros()"> COLECCIONES</label>
                <label><input type="checkbox" name="coleccion" value="CÓMICS" onchange="aplicarFiltros()"> CÓMICS</label>
                <label><input type="checkbox" name="coleccion" value="MÚSICA" onchange="aplicarFiltros()"> MÚSICA</label>
            </div>
        </div>
        
        <div class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Disponibilidad</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="disponibilidad" value="0" onchange="aplicarFiltros()"> Disponible</label>
                <label><input type="checkbox" name="disponibilidad" value="1" onchange="aplicarFiltros()"> No disponible</label>
            </div>
        </div>
    </section>

    <div class="contenedor-busqueda">
        <?php if (!empty($buscar)): ?>
            <div class="resultado-info">
                
                <strong>Resultados para: "<?php echo htmlspecialchars($buscar); ?>"</strong>
                <p><?php echo count($resultados); ?> libro(s) encontrado(s)</p>
                <a href="index.php">Volver al inicio</a>
            </div>

            <?php if (count($resultados) > 0): ?>
                <main class="vista-grid" id="mainContent">
                    <?php foreach ($resultados as $libro): 
                        $estado = ($libro['estado_de_actividad'] == 0) ? 'disponible' : 'no-disponible';
                        $estadoTexto = ($libro['estado_de_actividad'] == 0) ? 'Disponible' : 'No disponible';
                    ?>
                    <article data-titulo="<?php echo htmlspecialchars($libro['titulo']); ?>" data-estado="<?php echo $libro['estado_de_actividad']; ?>">
                        <section class="catalogo">
                            <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="<?php echo htmlspecialchars($libro['titulo']); ?>" class="portada-catalogo">
                            <a href="verlibro.php?id=<?php echo $libro['id_libro']; ?>" class="<?php echo $libro['ubicacion_por_colores']; ?>"><?php echo htmlspecialchars($libro['titulo']); ?></a>
                        </section>
                    </article>
                    <?php endforeach; ?>
                </main>

                <div class="vista-lista" id="vistaLista">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>ISBN</th>
                                <th>Colección</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $libro): 
                                $estado = ($libro['estado_de_actividad'] == 0) ? 'disponible' : 'no-disponible';
                                $estadoTexto = ($libro['estado_de_actividad'] == 0) ? 'Disponible' : 'No disponible';
                            ?>
                            <tr onclick="irAlLibro(<?php echo $libro['id_libro']; ?>)" style="cursor: pointer;">
                                <td><strong><?php echo htmlspecialchars($libro['titulo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                                <td><span class="<?php echo strtolower($libro['ubicacion_por_colores']); ?>" style="padding: 5px 10px; border-radius: 5px;"><?php echo htmlspecialchars($libro['ubicacion_por_colores']); ?></span></td>
                                <td><span class="estado-badge <?php echo $estado; ?>"><?php echo $estadoTexto; ?></span></td>
                                <td style="display: flex; gap: 8px;">
                                    <a href="verlibro.php?id=<?php echo $libro['id_libro']; ?>" style="color: #602b06; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #ffca0c; border-radius: 4px; font-size: 0.85rem;">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="sin-resultados">
                    <p>No se encontraron libros que coincidan con tu búsqueda.</p>
                    <p>Intenta con otros términos.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="sin-resultados">
                <p>Ingresa un término de búsqueda en la barra superior.</p>
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
        let vistaActual = 'grid';
        
        function toggleFiltro(button) {
            const contenido = button.nextElementSibling;
            if (contenido.style.display === 'none' || contenido.style.display === '') {
                contenido.style.display = 'block';
            } else {
                contenido.style.display = 'none';
            }
        }
        
        function aplicarFiltros() {
            const coleccionesSeleccionadas = Array.from(document.querySelectorAll('input[name="coleccion"]:checked')).map(el => el.value);
            const disponibilidadSeleccionada = Array.from(document.querySelectorAll('input[name="disponibilidad"]:checked')).map(el => el.value);
            
            // Mapear nombres de colecciones a sus clases CSS
            const mapeoClases = {
                'INFANTIL Y 1o CICLO': 'verde',
                '2o 3o CICLO': 'naranja',
                'ANIMALES Y NATURALEZA': 'azul',
                'VALORES': 'rojo',
                'EMOCIONES': 'rosa',
                'IGUALDAD': 'violeta',
                'INGLÉS': 'amarillo',
                'COLECCIONES': 'marron',
                'CÓMICS': 'blanco',
                'MÚSICA': 'negro'
            };
            
            // Filtrar artículos del grid
            document.querySelectorAll('main.vista-grid article').forEach(articulo => {
                const link = articulo.querySelector('.catalogo a');
                const colorClase = link.className;
                const estado = articulo.getAttribute('data-estado');
                let mostrar = true;
                
                // Filtro por colección
                if (coleccionesSeleccionadas.length > 0) {
                    const coleccionEncontrada = coleccionesSeleccionadas.some(col => colorClase === mapeoClases[col]);
                    mostrar = mostrar && coleccionEncontrada;
                }
                
                // Filtro por disponibilidad
                if (disponibilidadSeleccionada.length > 0 && mostrar) {
                    mostrar = disponibilidadSeleccionada.includes(estado);
                }
                
                articulo.style.display = mostrar ? '' : 'none';
            });
            
            // Filtrar filas de la tabla
            document.querySelectorAll('.vista-lista tbody tr').forEach(fila => {
                const coleccionSpan = fila.querySelector('td:nth-child(4) span');
                const estadoSpan = fila.querySelector('.estado-badge');
                let mostrar = true;
                
                // Filtro por colección
                if (coleccionesSeleccionadas.length > 0 && coleccionSpan) {
                    const claseColeccion = coleccionSpan.className;
                    const coleccionEncontrada = coleccionesSeleccionadas.some(col => claseColeccion === mapeoClases[col]);
                    mostrar = mostrar && coleccionEncontrada;
                }
                
                // Filtro por disponibilidad
                if (disponibilidadSeleccionada.length > 0 && estadoSpan && mostrar) {
                    const esDisponible = estadoSpan.classList.contains('disponible') ? '0' : '1';
                    mostrar = disponibilidadSeleccionada.includes(esDisponible);
                }
                
                fila.style.display = mostrar ? '' : 'none';
            });
        }
        
        function toggleVista() {
            vistaActual = vistaActual === 'grid' ? 'lista' : 'grid';
            cambiarVista(vistaActual);
        }
        
        function cambiarVista(vista) {
            const mainContent = document.getElementById('mainContent');
            const vistaLista = document.getElementById('vistaLista');
            const btnToggle = document.getElementById('btnToggleVista');
            
            if (vista === 'grid') {
                mainContent.style.display = 'grid';
                vistaLista.style.display = 'none';
                mainContent.classList.add('vista-grid');
                btnToggle.textContent = 'Vista Lista';
            } else {
                mainContent.style.display = 'none';
                vistaLista.style.display = 'block';
                mainContent.classList.remove('vista-grid');
                btnToggle.textContent = 'Vista Cuadricula';
            }
            
            localStorage.setItem('vistaPreferida', vista);
        }
        
        function irAlLibro(id) {
            window.location.href = 'verlibro.php?id=' + id;
        }
        
        window.addEventListener('load', function() {
            const vistaGuardada = localStorage.getItem('vistaPreferida') || 'grid';
            vistaActual = vistaGuardada;
            cambiarVista(vistaGuardada);
        });
    </script>
</body>
</html>
