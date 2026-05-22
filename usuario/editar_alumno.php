<?php
session_start();
require '../conexion.php';
require '../control_usuario_profe.php';

$mensaje = "";
$error = "";
$exito = "";

// 1. OBTENER EL ID DEL ALUMNO (Indispensable para cargar y para guardar)
if (isset($_REQUEST['id'])) {
    $id_alumnado = intval($_REQUEST['id']);
} else {
    header("Location: ver-alumno.php");
    exit();
}

// 2. LÓGICA PARA GUARDAR CAMBIOS (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updates = [];
    
    // Solo actualizamos si el campo no está vacío
    if (!empty($_POST['nombre'])) {
        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $updates[] = "nombre='$nombre'";
    }
    if (!empty($_POST['apellidos'])) {
        $apellidos = mysqli_real_escape_string($conn, $_POST['apellidos']);
        $updates[] = "apellidos='$apellidos'";
    }
    if (!empty($_POST['edad'])) {
        $edad = intval($_POST['edad']);
        $updates[] = "edad=$edad";
    }
    
    // Si cambia la clase, hay que regenerar el código de carnet
    $nueva_clase = $_POST['clase'] ?? '';
    if (!empty($nueva_clase)) {
        $clase_esc = mysqli_real_escape_string($conn, $nueva_clase);
        $updates[] = "clase='$clase_esc'";

        // Mapeo de códigos de clase
        $mapa_clases = [
            "1º Infantil" => "1IN", "2º Infantil" => "2IN", "3º Infantil" => "3IN", "4º Infantil" => "4IN",
            "1º Primaria" => "1PR", "2º Primaria" => "2PR", "3º Primaria" => "3PR", "4º Primaria" => "4PR",
            "5º Primaria" => "5PR", "6º Primaria" => "6PR"
        ];
        
        if (isset($mapa_clases[$nueva_clase])) {
            $codigo_clase = $mapa_clases[$nueva_clase];
            $nuevo_codigo_carnet = $codigo_clase . '-' . str_pad($id_alumnado, 5, '0', STR_PAD_LEFT);
            $updates[] = "codigo_de_carnet='$nuevo_codigo_carnet'";
        }
    }

    if (!empty($updates)) {
        $sql_update = "UPDATE alumnado SET " . implode(', ', $updates) . " WHERE id_alumnado=$id_alumnado";
        if (mysqli_query($conn, $sql_update)) {
            header("Location: ver-alumno.php?mensaje=Alumno actualizado correctamente");
            exit();
        } else {
            $error = "Error al actualizar: " . mysqli_error($conn);
        }
    } else {
        $error = "No se realizaron cambios.";
    }
}

// 3. LÓGICA PARA CARGAR DATOS (GET) - Se ejecuta siempre para mostrar los datos en el form
$sql = "SELECT * FROM alumnado WHERE id_alumnado = $id_alumnado";
$resultado = mysqli_query($conn, $sql);
$alumno = mysqli_fetch_assoc($resultado);

if (!$alumno) {
    header("Location: ver-alumno.php?error=Alumno no encontrado");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Alumnado - Biblioteca</title>
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

    <div class="contenedor-formulario">
        <div class="form-caja">
            <h2 style="text-align: center; color: #602b06; margin-top: 0;">Editar Alumnado</h2>
            
            <?php if ($error): ?>
                <div class="mensaje-error" style="color:red; text-align:center;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="editar_alumno.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id_alumnado; ?>">

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($alumno['apellidos']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="edad">Edad:</label>
                    <input type="number" id="edad" name="edad" value="<?php echo htmlspecialchars($alumno['edad']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="clase">Curso Actual: <strong><?php echo htmlspecialchars($alumno['clase']); ?></strong></label>
                    <select id="clase" name="clase">
                        <option value="">-- Cambiar curso (Opcional) --</option>
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
                </div>

                <button type="submit" class="btn-submit">Guardar Cambios</button>
                <a href="ver-alumno.php" class="btn-cancelar" style="display:block; text-align:center; margin-top:10px;">Cancelar y volver</a>
            </form>
        </div>
    </div>
</body>
</html>