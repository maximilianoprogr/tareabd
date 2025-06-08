<?php
// Inicia la sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../php/index.php'); // Redirigir al índice si no está autenticado
    exit(); // Finalizar la ejecución del script
}

// Incluir el archivo de conexión a la base de datos
include('../php/db_connection.php');

// Consulta para obtener los datos del usuario autenticado
$sql = "SELECT * FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']); // Asignar el ID del usuario a la consulta
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc(); // Obtener los datos del usuario
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Establecer la codificación de caracteres a UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configurar la página para que sea responsive -->
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $user['userid']; ?>!</h1>
    <p>Email: <?php echo $user['email']; ?></p>
    <a href="../php/logout.php">Logout</a>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
