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
        // Actualizar la consulta para determinar el rol del usuario sin usar la columna tipo en Usuario
        $sql = "SELECT u.*, CASE 
                    WHEN EXISTS (SELECT 1 FROM Autor WHERE rut = u.rut) THEN 'Autor'
                    WHEN EXISTS (SELECT 1 FROM Revisor WHERE rut = u.rut) THEN 'Revisor'
                    ELSE 'Jefe Comite de Programa'
                END AS rol 
                FROM Usuario u WHERE u.rut = ?";
        $stmt = $pdo->prepare($sql); // Asegúrate de que $pdo esté definido correctamente
        $stmt->execute([$rut]);
        $user = $stmt->fetch();

        if ($user) {
            // Cambiar la validación para comparar directamente las contraseñas sin usar `password_verify`
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['rut']; // Guardar el ID del usuario en la sesión
                $_SESSION['usuario'] = $user['rut']; // Guardar el nombre del usuario en la sesión
                // Guardar el rol del usuario en la sesión
                $_SESSION['rol'] = $user['rol']; // Determinar el rol dinámicamente
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

    <?php
    // Depuración: Verificar si el rol se está configurando correctamente
    if (isset($_SESSION['rol'])) {
        echo "<p style='color: green;'>Rol configurado en la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";
    } else {
        echo "<p style='color: red;'>Error: El rol no se configuró en la sesión.</p>";
    }
    ?>
</body>
</html>
