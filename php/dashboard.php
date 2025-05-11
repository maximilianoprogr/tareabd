<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión iniciada, redirige al login
    header("Location: ../php/login.php");
    exit();
}

echo "Bienvenido, " . $_SESSION['usuario']; // Muestra el nombre de usuario
?>

<br><br>
<a href="../php/logout.php">Cerrar sesión</a> <!-- Opción para cerrar sesión -->

<br>
<form action="../php/buscar_articulos.php" method="GET">
    <input type="text" name="query" placeholder="Buscar artículos...">
    <button type="submit">Buscar</button>
</form>

<br>
<div class="acciones">
    <button onclick="location.href='enviar_articulo.php'">Enviar Artículo</button>
    <button onclick="location.href='acceso_articulo.php'">Acceso al Artículo</button>
    <button onclick="location.href='gestionar_revisores.php'">Gestión de Revisores</button>
    <button onclick="location.href='asignar_articulos.php'">Asignación de Artículos</button>
</div>
<br>
<a href="../php/gestionar_revisores.php">Gestionar Revisores</a>
<a href="../php/asignar_articulos.php">Asignar Artículos</a>
</body>
</html>
