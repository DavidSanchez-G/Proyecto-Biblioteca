<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Sesion</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main id="inicio-sesion" style="max-height: 40%;">
        <section id="formulario">
            <img src="img/logo.png" alt="Logo">
            <p class="inicio-sesion-p">Bienvenidos</p>
            <form class="formulario-inicio-sesion" method="POST" action="control_iniciosesion.php">
                <label for="usuario">Nombre</label>
                <input type="text" name="usuario" id="usuario"><br><br>

                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password"><br><br>

                <a href="index.php">Volver</a>
                <input type="submit" class="boton-inicio-sesion" value="Log-in"><br>
            </form>
            <?php
            session_start();    
            include("conexion.php");
            if(!empty($_SESSION["inicio"])) {
                echo "<p>".$_SESSION["inicio"]."</p>";
                unset($_SESSION["inicio"]);
            } 
            session_destroy();
            ?>
        </section>
        <section id="img-inicio">
        </section>
        
    </main>
    <footer style="margin-top: 0px;">
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