<?php
session_start();
require '../conexion.php';
require '../control_usuario.php';

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? mysqli_real_escape_string($conn, $_GET['busqueda']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuarios</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Estilos específicos para la tabla de usuarios */
        .rol-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
            color: white;
            background-color: #602b06; /* Color por defecto */
        }
        /* Puedes personalizar colores según el rol si quieres */
        .rol-administrador { background-color: #f44336; } /* Rojo */
        .rol-bibliotecario { background-color: #2196F3; } /* Azul */
        .rol-lector { background-color: #4CAF50; }        /* Verde */
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
                <form action="ver-usuarios.php" method="GET">
                    <input type="text" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar usuario..." required>
                    <button>🔍</button>
                </form>
            </li>
        </ul>
    </nav>
    <main style="width: 100vw; margin: 0; padding: 0; height: 80vh;">
    <?php if(!empty($busqueda)): ?>
        <div style="text-align: center; margin-top: 140px; color: #602b06;">
            Resultados para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
            <a href="ver-usuarios.php" style="font-size: 0.9rem; margin-left: 10px; color: #2196F3;">(Ver todos)</a>
        </div>
        <article class="vista-lista" style="display: block; margin-top: 20px;">
    <?php else: ?>
        <article class="vista-lista" style="display: block; margin-top:200px;">
    <?php endif; ?>

    <?php if (isset($_GET['mensaje'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Username</th>
                    <th>Carnet</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    <?php
        // --- CONSULTA SQL ---
        // 1. Seleccionamos campos de usuario (u)
        // 2. Hacemos JOIN con la tabla rol (r) para sacar el nombre del rol
        // NOTA: Asumo que en la tabla 'rol' el campo del nombre se llama 'nombre'. 
        // Si se llama 'tipo', 'descripcion' o 'rol', cámbialo en "r.nombre AS nombre_rol"
        
        $sql = "SELECT 
                    u.id_usuario, 
                    u.codigo_de_carnet, 
                    u.nombre, 
                    u.username, 
                    r.nombre AS nombre_rol 
                FROM usuario u
                LEFT JOIN rol r ON u.id_rol = r.id_rol";
        
        // Filtro de búsqueda
        if (!empty($busqueda)) {
            $sql .= " WHERE u.nombre LIKE '%$busqueda%' 
                      OR u.username LIKE '%$busqueda%'";
        }

        $sql .= " ORDER BY u.nombre ASC";
        
        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            echo "<tr><td colspan='4' style='text-align: center; color: red;'>Error en la consulta: " . mysqli_error($conn) . "</td></tr>";
        } else {
            if (mysqli_num_rows($result) == 0) {
                echo "<tr><td colspan='4' style='text-align: center; color: #666;'>No hay usuarios registrados</td></tr>";
            } else {
                while ($usuario = mysqli_fetch_assoc($result)) {
                    // Generamos una clase CSS basada en el nombre del rol para darle color (opcional)
                    // Ej: si el rol es 'Administrador', la clase será 'rol-administrador'
                    $claseRol = 'rol-' . strtolower($usuario['nombre_rol']);
    ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['codigo_de_carnet']); ?></td>
                    <td>
                        <span class="rol-badge <?php echo $claseRol; ?>">
                            <?php echo htmlspecialchars($usuario['nombre_rol']); ?>
                        </span>
                    </td>
                    <td style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #2196F3; border-radius: 4px; font-size: 0.85rem;">Editar</a>
                        
                        <a href="eliminar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" 
                           style="color: white; text-decoration: none; font-weight: bold; padding: 6px 12px; background-color: #f44336; border-radius: 4px; font-size: 0.85rem;"
                           onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?')">
                           Eliminar
                        </a>
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