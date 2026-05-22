<?php
// --- 1. CABECERAS PARA PROHIBIR LA CACHÉ (OBLIGATORIO AL PRINCIPIO) ---
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
session_start();

require '../conexion.php';
require '../control_usuario_profe.php';

function getColorCarnet($codigo) {
    $colores = [
        'verde' => '4CAF50', 'naranja' => 'FF9800', 'azul' => '2196F3',
        'rojo' => 'f44336', 'rosa' => 'E91E63', 'violeta' => '9C27B0',
        'amarillo' => 'FDD835', 'marrón' => '795548', 'blanco' => 'CCCCCC', 'negro' => '333333'
    ];
    return $colores[strtolower($codigo)] ?? 'CCCCCC';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Ver Alumnos</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .btn-sancionar { background-color: #f44336; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: inline-block; }
        .btn-sancionar:hover { background-color: #d32f2f; }
        .btn-restaurar { background-color: #4CAF50; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: inline-block; }
        .btn-restaurar:hover { background-color: #388E3C; }
        .btn-editar { background-color: #2196F3; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: inline-block; }
        .badge-sancionado { background-color: #ffcccc; color: #721c24; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
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
                <form action="ver-alumno.php" method="GET">
                    <input type="text" name="buscar" placeholder="Nombre, apellido o carnet..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>

    <main style="width: 100vw; margin: 0; padding: 0; min-height: 80vh;">
    <section class="filtros-container" style="margin-top: 130px; position: relative; z-index: 100; padding: 0 20px;">
        <article class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Clase</button>
            <div class="filtro-contenido" style="display: none;">
                <?php 
                $clases = ["1º Infantil", "2º Infantil", "3º Infantil", "4º Infantil", "1º Primaria", "2º Primaria", "3º Primaria", "4º Primaria", "5º Primaria", "6º Primaria"];
                foreach($clases as $c) {
                    echo "<label><input type='checkbox' name='clase' value='$c' onchange='aplicarFiltros()'> $c</label>";
                }
                ?>
            </div>
        </article>
        
        <article class="filtro-desplegable">
            <button class="filtro-titulo" onclick="toggleFiltro(this)"> Filtrar por Estado</button>
            <div class="filtro-contenido" style="display: none;">
                <label><input type="checkbox" name="estado" value="0" onchange="aplicarFiltros()"> Activo (No Sancionado)</label>
                <label><input type="checkbox" name="estado" value="1" onchange="aplicarFiltros()"> Sancionado</label>
            </div>
        </article>
    </section>

    <?php if (isset($_GET['mensaje'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 20px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>

    <article class="vista-lista" id="vistaLista" style="display: block; margin: 20px;">
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
    <?php
        // --- CAMBIO CLAVE: BÚSQUEDA POR CARNET ---
        $busqueda = isset($_GET['buscar']) ? mysqli_real_escape_string($conn, $_GET['buscar']) : '';
        $sql = "SELECT * FROM alumnado";
        if (!empty($busqueda)) {
            $sql .= " WHERE nombre LIKE '%$busqueda%' 
                      OR apellidos LIKE '%$busqueda%' 
                      OR codigo_de_carnet LIKE '%$busqueda%'"; // Busca por carnet
        }
        $sql .= " ORDER BY nombre ASC";
        
        $result = mysqli_query($conn, $sql);
        
        while ($alumno = mysqli_fetch_assoc($result)) {
            // --- CAMBIO CLAVE: DEFINICIÓN DE ESTADO PARA FILTROS JS ---
            // '0' para Activo, '1' para Sancionado
            $estadoNum = (strtolower($alumno['estado_de_sancion']) == 'sancionado') ? '1' : '0';
            $claseEstado = ($estadoNum == '1') ? 'badge-sancionado' : 'badge-activo';
            
            // Color dinámico del carnet
            $colorHex = getColorCarnet($alumno['codigo_de_carnet']);
    ?>
            <tr data-clase="<?php echo htmlspecialchars($alumno['clase']); ?>" 
                data-estado="<?php echo $estadoNum; ?>">
                
                <td><strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong></td>
                <td><?php echo htmlspecialchars($alumno['apellidos']); ?></td>
                <td><?php echo htmlspecialchars($alumno['clase']); ?></td>
                <td><?php echo htmlspecialchars($alumno['edad']); ?></td>
                <td>
                    <span style="background-color: #<?php echo $colorHex; ?>; padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; text-shadow: 1px 1px 1px rgba(0,0,0,0.3);">
                        <?php echo htmlspecialchars($alumno['codigo_de_carnet']); ?>
                    </span>
                </td>
                <td>
                    <span class="<?php echo $claseEstado; ?>">
                        <?php echo htmlspecialchars($alumno['estado_de_sancion']); ?>
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="editar_alumno.php?id=<?php echo $alumno['id_alumnado']; ?>" class="btn-editar">Editar</a>
                        <a href="sancionar.php?id=<?php echo $alumno['id_alumnado']; ?>" class="btn-restaurar">Modificar sanción</a>
                </td>
            </tr>
    <?php
        }
    ?>
            </tbody>
        </table>
    </article>
    
    <script>
        function toggleFiltro(button) {
            const contenido = button.nextElementSibling;
            contenido.style.display = (contenido.style.display === 'none' || contenido.style.display === '') ? 'block' : 'none';
        }
        
        function aplicarFiltros() {
            const clasesSeleccionadas = Array.from(document.querySelectorAll('input[name="clase"]:checked')).map(el => el.value);
            const estadosSeleccionados = Array.from(document.querySelectorAll('input[name="estado"]:checked')).map(el => el.value);
            
            document.querySelectorAll('.vista-lista tbody tr').forEach(fila => {
                const fClase = fila.getAttribute('data-clase'); 
                const fEstado = fila.getAttribute('data-estado');
                
                let cumpleClase = clasesSeleccionadas.length === 0 || clasesSeleccionadas.includes(fClase);
                let cumpleEstado = estadosSeleccionados.length === 0 || estadosSeleccionados.includes(fEstado);
                
                fila.style.display = (cumpleClase && cumpleEstado) ? '' : 'none';
            });
        }
    </script>
    </main>
    <footer>
        <section id="footer-cabecera"><a href="https://ceipandresmanjon.catedu.es"><h2>CEIP Andres Manjon</h2></a></section>
        <section class="footer-section"><p>C. de las Delicias, 90, Delicias.</p><p>50017 Zaragoza.</p></section>
        <section class="footer-section"><p>Tlf: +34 976 331 728</p><p>Lunes a viernes: 8:30 a 15:00</p></section>
    </footer>
</body>
</html>