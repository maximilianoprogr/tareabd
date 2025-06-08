<?php
// Iniciar sesión para verificar si el usuario ya está autenticado
session_start();
if (isset($_SESSION['user_id'])) {
    // Redirigir al dashboard si ya hay una sesión activa
    header('Location: ../php/dashboard.php'); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <!-- Formulario para iniciar sesión -->
    <form action="../php/login_process.php" method="POST">
        <input type="text" name="userid" placeholder="User ID" required><br><br> <!-- Campo para ingresar el ID de usuario -->
        <input type="password" name="password" placeholder="Password" required><br><br> <!-- Campo para ingresar la contraseña -->
        <input type="submit" value="Login"> <!-- Botón para enviar el formulario -->
    </form>
    <br>
    <!-- Enlace para registrarse -->
    <a href="../php/register.php">Register</a>
    <br>
    <!-- Enlace para volver al dashboard principal -->
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>