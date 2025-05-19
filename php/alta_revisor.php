<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../php/conexion.php'); 

echo "<p style='color: green;'>El archivo alta_revisor.php se ha cargado correctamente.</p>";

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'Jefe Comite de Programa')) {
    echo "<p style='color: red;'>Acceso denegado: Usuario no autorizado.</p>";
    header("Location: ../php/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Revisor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h1 style="font-size: 18px; color: #333;">Alta de Revisor</h1>

    <form method="POST" action="gestionar_revisores.php" style="margin-top: 20px; border: 1px solid #ccc; padding: 15px;">
        <input type="hidden" name="action" value="create">
        <label for="nombre" style="font-size: 14px; display: block; margin-bottom: 5px;">Nombre:</label>
        <input type="text" id="nombre" name="nombre" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="Ingrese el nombre">
        <label for="email" style="font-size: 14px; display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="email" name="email" style="width: 100%; padding: 8px; margin-bottom: 10px;" required placeholder="usuario@dominio.com">
        <label for="userid" style="font-size: 14px; display: block; margin-bottom: 5px;">Usuario ID:</label>
        <input type="text" id="userid" name="userid" style="width: 100%; padding: 8px; margin-bottom: 10px;">
        <label for="password" style="font-size: 14px; display: block; margin-bottom: 5px;">Contraseña:</label>
        <input type="password" id="password" name="password" style="width: 100%; padding: 8px; margin-bottom: 10px;">
        <button type="submit" style="font-size: 14px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Guardar</button>
    </form>

    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none; display: block; margin-top: 20px;">Volver al inicio</a>
</body>
</html>
