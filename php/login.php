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
        // Usar la vista en vez de la consulta con CASE
        $sql = "SELECT * FROM vista_usuarios_login WHERE rut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut]);
        $user = $stmt->fetch();

        if ($user) {
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['rut'];
                $_SESSION['usuario'] = $user['rut'];
                $_SESSION['rol'] = $user['rol'];
                header("Location: ../php/index.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="../php/login.php" method="post">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Iniciar sesión" class="btn">
        </form>
        <p>¿No tienes una cuenta? <a href="../php/register.php">Regístrate aquí</a></p>
        <a href="dashboard.php" class="back-link">Volver al inicio</a>
    </div>
</body>
</html>
