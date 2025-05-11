<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirigir al login si no está autenticado
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Página de Artículos</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Archivo CSS -->
</head>
<body>
    <?php include('navegacion.php'); ?> <!-- Incluir la barra de navegación -->
    <div id="content">
        <h1>Bienvenido a la Plataforma de Artículos</h1>
        <!-- Contenido inicial -->
    </div>
    <script src="scripts.js"></script> <!-- Archivo JavaScript -->
</body>
</html>
