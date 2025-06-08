<?php
// Inicia la sesión para verificar si el usuario está autenticado.
session_start();

// Redirige al usuario al login si no está autenticado.
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtiene los parámetros de la URL para identificar al revisor y al usuario.
$revisor = isset($_GET['revisor']) ? htmlspecialchars($_GET['revisor']) : 'Desconocido';
$usuario = isset($_GET['usuario']) ? htmlspecialchars($_GET['usuario']) : 'Desconocido';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento HTML -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisor Incorrecto</title>
    <!-- Enlace al archivo de estilos CSS -->
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="container">
        <!-- Mensaje de acceso restringido -->
        <h1>Acceso Restringido</h1>
        <p>Hola <strong><?php echo $usuario; ?></strong>, solo el revisor <strong><?php echo $revisor; ?></strong> puede opinar sobre este artículo.</p>
        <!-- Enlace para volver a la página de acceso a artículos -->
        <a href="acceso_articulo.php" class="button">Volver</a>
    </div>
</body>
</html>
