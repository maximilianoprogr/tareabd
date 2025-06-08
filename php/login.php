<?php
// Iniciar sesión para manejar autenticación de usuarios
session_start();

// Verificar si el usuario ya ha iniciado sesión
if (isset($_SESSION['user_id'])) {
    header('Location: ../php/index.php'); // Redirige al inicio si ya está autenticado
    exit();
}

// Incluir archivo de conexión a la base de datos
include('../php/conexion.php');

// Variable para almacenar mensajes de error o éxito
$message = ""; 

// Manejar solicitudes POST para iniciar sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'] ?? null; // RUT del usuario
    $password = $_POST['password'] ?? null; // Contraseña del usuario

    // Validar que ambos campos estén completos
    if ($rut && $password) {

        // Preparar la consulta SQL para buscar al usuario por su RUT
        $sql = "SELECT * FROM vista_usuarios_login WHERE rut = ?";  // Vista de usuarios para login
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut]);
        $user = $stmt->fetch();

        // Verificar si se encontró un usuario con el RUT proporcionado
        if ($user) {
            // Comparar la contraseña ingresada con la almacenada en la base de datos
            if ($password === $user['password']) {
                // Almacenar datos del usuario en la sesión
                $_SESSION['user_id'] = $user['rut'];
                $_SESSION['usuario'] = $user['rut'];
                $_SESSION['rol'] = $user['rol'];

                header("Location: ../php/index.php"); // Redirigir al inicio
                exit();
            } else {
                $message = "Contraseña incorrecta."; // Mensaje de error si la contraseña es incorrecta
            }
        } else {
            $message = "Usuario no encontrado."; // Mensaje de error si no se encuentra el usuario
        }
    } else {
        $message = "Por favor, complete todos los campos."; // Mensaje de error si faltan campos
    }
}

// Código HTML para el formulario de inicio de sesión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Configuración de codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hacer que la página sea responsive -->
    <title>Login</title> <!-- Título de la página -->
    <link rel="stylesheet" href="../css/login.css"> <!-- Enlace al archivo de estilos CSS -->
</head>
<body>
    <div class="login-container"> <!-- Contenedor principal del formulario de login -->
        <h2>Iniciar Sesión</h2> <!-- Título del formulario -->
        <?php if (!empty($message)): ?>
            <p class="message"> <!-- Mensaje de error o éxito -->
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form action="../php/login.php" method="post"> <!-- Formulario de inicio de sesión -->
            <label for="rut">RUT:</label> <!-- Etiqueta para el campo RUT -->
            <input type="text" id="rut" name="rut" required> <!-- Campo de entrada para el RUT -->

            <label for="password">Contraseña:</label> <!-- Etiqueta para el campo Contraseña -->
            <input type="password" id="password" name="password" required> <!-- Campo de entrada para la contraseña -->

            <input type="submit" value="Iniciar sesión" class="btn"> <!-- Botón para enviar el formulario -->
        </form>
        <p>¿No tienes una cuenta? <a href="../php/register.php">Regístrate aquí</a></p> <!-- Enlace para registrarse -->
        <a href="dashboard.php" class="back-link">Volver al inicio</a> <!-- Enlace para volver al inicio -->
    </div>
</body>
</html>
