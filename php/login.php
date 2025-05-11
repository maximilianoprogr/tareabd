<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirigir al index si ya está autenticado
    exit();
}

include('conexion.php'); // Asegúrate de que este archivo define correctamente la conexión PDO

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
            // Verificar si la contraseña es correcta
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['rut']; // Guardar el ID del usuario en la sesión
                $_SESSION['usuario'] = $user['rut']; // Guardar el nombre del usuario en la sesión
                header("Location: index.php"); // Redirigir al index
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
    <form action="login.php" method="post">
        <label for="rut">RUT:</label>
        <input type="text" id="rut" name="rut" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Iniciar sesión">
    </form>
    <br>
    <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
</body>
</html>
