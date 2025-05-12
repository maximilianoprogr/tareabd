<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Archivo CSS externo -->
</head>
<body>
<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión iniciada, redirige al login
    header("Location: login.php");
    exit();
}

echo "<div class='welcome'>Bienvenido, " . $_SESSION['usuario'] . "</div>"; // Muestra el nombre de usuario
?>

<div class="container">
    <a href="logout.php" class="logout-btn">Cerrar sesión</a> <!-- Opción para cerrar sesión -->

    <form action="buscar_articulos.php" method="GET" class="search-form">
        <input type="text" name="query" placeholder="Buscar artículos..." class="search-input">
        <button type="submit" class="search-btn">Buscar</button>
    </form>

    <div class="acciones">
        <button onclick="location.href='enviar_articulo.php'" class="action-btn">Enviar Artículo</button>
        <button onclick="location.href='acceso_articulo.php'" class="action-btn">Acceso al Artículo</button>
        <button onclick="location.href='gestionar_revisores.php'" class="action-btn">Gestión de Revisores</button>
        <button onclick="location.href='asignar_articulos.php'" class="action-btn">Asignación de Artículos</button>
    </div>
</div>
</body>
</html>
