<?php
// procesarLogin.php
session_start();
require '../conexion.php'; // Incluye la conexión 
require '../control_usuario.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo</title>
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
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px;">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <main class="vista-grid" id="mainContent">
        <!-- Esto esta automatizado con PHP -->
    <?php
        $sql = "SELECT * FROM libro ";
        $libros = mysqli_query($conn, $sql);

        while ($libro = mysqli_fetch_array($libros)) {   
        ?>
        <article data-titulo="<?php echo htmlspecialchars($libro['titulo']); ?>" data-estado="<?php echo $libro['estado_de_actividad']; ?>">
            <section class="catalogo" >
                <img src="../<?php echo $libro['portada']; ?>" alt="<?php echo $libro['titulo']; ?>" class="portada-catalogo"><br>
                <a href="verlibro.php?id=<?php echo $libro['id_libro']; ?>" class="<?php echo $libro['ubicacion_por_colores']; ?>"><?php echo $libro['titulo']; ?></a>
                <section class="catalogo-acciones">
                    <a href="editar.php?id=<?php echo $libro['id_libro']; ?>" class="btn-editar">Editar</a>
                    <a href="eliminar_libro.php?id=<?php echo $libro['id_libro']; ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este libro?')">Eliminar</a>
                    <a href="asignar.php?id=<?php echo $libro['id_libro']; ?>" class="btn-asignar">Asignar</a>
                </section>
            </section>
        </article>
    <?php
        }
        ?>
    </main>
    
    <div class="vista-lista" id="vistaLista">
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor/Editorial</th>
                    <th>ISBN</th>
                    <th>Colección</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
    <?php
        // Resetear el resultado para mostrar nuevamente
        $sql = "SELECT * FROM libro ";
        $libros = mysqli_query($conn, $sql);
        
        // --- DEFINICIÓN DE NOMBRES DE COLECCIONES ---
        // Esto traduce el color al nombre real que quieres mostrar
        $nombresColecciones = [
            'verde'    => 'INFANTIL Y 1º CICLO',
            'naranja'  => '2º Y 3º CICLO',
            'azul'     => 'ANIMALES Y NATURALEZA',
            'rojo'     => 'VALORES',
            'rosa'     => 'EMOCIONES',
            'violeta'  => 'IGUALDAD',
            'amarillo' => 'INGLÉS',
            'marron'   => 'COLECCIONES', // Asegúrate de que en BD sea 'marron' sin tilde o con tilde según tus datos
            'marrón'   => 'COLECCIONES', // Por si acaso
            'blanco'   => 'CÓMICS',
            'negro'    => 'MÚSICA'
        ];

        while ($libro = mysqli_fetch_array($libros)) {
            $estado = ($libro['estado_de_actividad'] == 0) ? 'disponible' : 'no-disponible';
            $estadoTexto = ($libro['estado_de_actividad'] == 0) ? 'Disponible' : 'No disponible';
            
            // Obtenemos el color en minúsculas para buscar en la lista
            $color = strtolower($libro['ubicacion_por_colores']);
            
            // Si el color existe en nuestra lista, usamos el nombre, si no, dejamos el color original
            $textoColeccion = isset($nombresColecciones[$color]) ? $nombresColecciones[$color] : $libro['ubicacion_por_colores'];
    ?>
                <tr onclick="irAlLibro(<?php echo $libro['id_libro']; ?>)" style="cursor: pointer;">
                    <td><strong><?php echo htmlspecialchars($libro['titulo']); ?></strong></td>
                    <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                    <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                    <td>
                        <span class="<?php echo $color; ?>" style="padding: 5px 10px; border-radius: 5px; display:inline-block; font-size: 0.8rem;">
                            <?php echo htmlspecialchars($textoColeccion); ?>
                        </span>
                    </td>
                    <td><span class="estado-badge <?php echo $estado; ?>"><?php echo $estadoTexto; ?></span></td>
                    <td style="display: flex; gap: 8px;">
                        <a href="verlibro.php?id=<?php echo $libro['id_libro']; ?>" style="color: #602b06; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #ffca0c; border-radius: 4px; font-size: 0.85rem;">Ver</a>
                        <a href="editar.php?id=<?php echo $libro['id_libro']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #4CAF50; border-radius: 4px; font-size: 0.85rem;">Editar</a>
                        <a href="eliminar_libro.php?id=<?php echo $libro['id_libro']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #f44336; border-radius: 4px; font-size: 0.85rem;" onclick="return confirm('¿Estás seguro de que quieres eliminar este libro?')">Eliminar</a>
                        <a href="asignar.php?id=<?php echo $libro['id_libro']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #2196F3; border-radius: 4px; font-size: 0.85rem;">Asignar</a>
                    </td>
                </tr>
    <?php
        }
    ?>
            </tbody>
        </table>
    </div>
    
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
            
            // Guardar preferencia en localStorage
            localStorage.setItem('vistaPreferida', vista);
        }
        
        function irAlLibro(id) {
            window.location.href = 'verlibro.php?id=' + id;
        }
        
        // Cargar vista preferida al abrir la página
        window.addEventListener('load', function() {
            const vistaGuardada = localStorage.getItem('vistaPreferida') || 'grid';
            vistaActual = vistaGuardada;
            cambiarVista(vistaGuardada);
        });
    </script>
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
