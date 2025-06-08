<?php
// Iniciar sesión para verificar si el usuario está autenticado
session_start();

// Verificar si no hay un usuario autenticado en la sesión
if (!isset($_SESSION['user_id'])) {
    // Redirigir al login si no hay sesión activa
    header('Location: login.php'); 
    // Finalizar la ejecución del script para evitar que se ejecute código adicional
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Establecer la codificación de caracteres a UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configurar la página para que sea responsive -->
    <title>Mi Página de Artículos</title> <!-- Título de la página -->
    <!-- Incluir archivo CSS para estilos de la página -->
    <link rel="stylesheet" href="../css/estilos.css"> 
</head>
<body>
    <div id="content"> <!-- Contenedor principal de la página -->
        <!-- Encabezado de bienvenida -->
        <h1>Bienvenido a la Plataforma de Artículos</h1>
        <p>Selecciona una opción del menú para comenzar.</p>
        <!-- Enlace para volver al dashboard principal -->
        <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
    </div>
    <script src="../js/scripts.js"></script> <!-- Incluir archivo JavaScript para funcionalidades -->
    <div>
        <?php include('../php/navegacion.php'); ?> <!-- Incluir archivo de navegación -->
    </div>
</body>
</html>
