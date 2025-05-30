<?php
session_start();
include('../php/conexion.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM Usuario WHERE rut = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_password = $_POST['password'];

    $hashed_password = password_hash($nuevo_password, PASSWORD_DEFAULT);
    $sql_update = "UPDATE Usuario SET password = ? WHERE rut = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$hashed_password, $user_id]);

    echo "Datos actualizados exitosamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuario</title>
</head>
<body>
    <h1>Gestión de Usuario</h1>
    <form method="POST">
        <label for="password">Nueva Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Actualizar">
    </form>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
