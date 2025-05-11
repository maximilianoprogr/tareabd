<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión iniciada, redirige al login
    header("Location: login.php");
    exit();
}

echo "Bienvenido, " . $_SESSION['usuario']; // Muestra el nombre de usuario
?>

<br><br>
<a href="logout.php">Cerrar sesión</a> <!-- Opción para cerrar sesión -->

<br>
<form action="buscar_articulos.php" method="GET">
    <input type="text" name="query" placeholder="Buscar artículos...">
    <button type="submit">Buscar</button>
</form>

<br>
<a href="gestionar_revisores.php">Gestionar Revisores</a>
<a href="asignar_articulos.php">Asignar Artículos</a>
