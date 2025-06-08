<?php
// Inicia la sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no está autenticado
    exit(); // Finalizar la ejecución del script
}

// Obtener el revisor desde los parámetros GET o asignar 'Desconocido' si no está definido
$revisor = isset($_GET['revisor']) ? htmlspecialchars($_GET['revisor']) : 'Desconocido';

// Obtener el ID del artículo desde los parámetros GET o asignar 'Desconocido' si no está definido
$id_articulo = isset($_GET['id_articulo']) ? htmlspecialchars($_GET['id_articulo']) : 'Desconocido';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Establecer la codificación de caracteres a UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configurar la página para que sea responsive -->
    <title>Evaluación Incompleta</title> <!-- Título de la página -->
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Archivo CSS externo para estilos -->
</head>
<body>
    <div class="container">
        <h1>Evaluación Incompleta</h1>
        <p>El revisor <strong><?php echo $revisor; ?></strong> aún no ha completado la evaluación para el artículo con ID <strong><?php echo $id_articulo; ?></strong>.</p>
        <a href="acceso_articulo.php" class="button">Volver</a>
    </div>
</body>
</html>
