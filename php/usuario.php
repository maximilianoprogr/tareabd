<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Incluir archivo de conexión a la base de datos
include('../php/conexion.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php"); // Redirigir al login si no está autenticado
    exit();
}

$user_id = $_SESSION['user_id']; // Obtener el ID del usuario actual

// Consultar los datos del usuario en la base de datos
$sql = "SELECT * FROM Usuario WHERE rut = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(); // Obtener los datos del usuario

// Procesar el formulario para actualizar la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_password = $_POST['password']; // Obtener la nueva contraseña del formulario

    // Encriptar la nueva contraseña
    $hashed_password = password_hash($nuevo_password, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $sql_update = "UPDATE Usuario SET password = ? WHERE rut = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$hashed_password, $user_id]);

    echo "Datos actualizados exitosamente."; // Mostrar mensaje de éxito
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Configuración de codificación de caracteres -->
    <title>Gestión de Usuario</title> <!-- Título de la página -->
</head>
<body>
    <h1>Gestión de Usuario</h1> <!-- Título principal de la página -->
    <form method="POST"> <!-- Formulario para actualizar la contraseña -->
        <label for="password">Nueva Contraseña:</label> <!-- Etiqueta para el campo de contraseña -->
        <input type="password" id="password" name="password" required><br><br> <!-- Campo de entrada para la nueva contraseña -->
        <input type="submit" value="Actualizar"> <!-- Botón para enviar el formulario -->
    </form>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a> <!-- Enlace para volver al inicio -->
</body>
</html>
