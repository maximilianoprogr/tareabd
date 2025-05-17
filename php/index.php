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
    <link rel="stylesheet" href="../css/estilos.css"> <!-- Archivo CSS -->
</head>
<body>
    <div id="content">
        <h1>Bienvenido a la Plataforma de Artículos</h1>
        <p>Selecciona una opción del menú para comenzar.</p>
        <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
    </div>
    <script src="../js/scripts.js"></script> <!-- Archivo JavaScript -->
    <div>
        <?php include('../php/navegacion.php'); ?> <!-- Incluir la barra de navegación -->
    </div>
</body>
</html>
