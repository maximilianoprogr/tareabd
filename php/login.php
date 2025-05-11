<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../php/index.php'); // Redirigir al index si ya está autenticado
    exit();
}

include('../php/conexion.php'); // Asegúrate de que este archivo define correctamente la conexión PDO

$message = ""; // Variable para almacenar mensajes

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($rut && $password) {
        // Usar sentencia preparada para evitar inyecciones SQL
        $sql = "SELECT * FROM Usuario WHERE rut = ?";
        $stmt = $pdo->prepare($sql); // Asegúrate de que $pdo esté definido correctamente
        $stmt->execute([$rut]);
        $user = $stmt->fetch();

        if ($user) {
            // Cambiar la validación para comparar directamente las contraseñas sin usar `password_verify`
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['rut']; // Guardar el ID del usuario en la sesión
                $_SESSION['usuario'] = $user['rut']; // Guardar el nombre del usuario en la sesión
                header("Location: ../php/index.php"); // Redirigir al index
                exit();
            } else {
                $message = "Contraseña incorrecta.";
            }
        } else {
            $message = "Usuario no encontrado.";
        }
    } else {
        $message = "Por favor, complete todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Formulario de Login</h2>
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="../php/login.php" method="post">
        <label for="rut">RUT:</label>
        <input type="text" id="rut" name="rut" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Iniciar sesión">
    </form>
    <br>
    <p>¿No tienes una cuenta? <a href="../php/register.php">Regístrate aquí</a></p>
    <a href="dashboard.php" style="font-family: Arial, sans-serif; font-size: 14px; color: #007BFF; text-decoration: none;">Volver al inicio</a>
</body>
</html>
