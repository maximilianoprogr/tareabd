<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../php/db_connection.php'); // Asegúrate de que este archivo define correctamente la variable $conn

$message = ""; // Variable para almacenar mensajes
$showLoginButton = false; // Variable para controlar la visibilidad del botón de login

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'] ?? null;
    $usuario = $_POST['usuario'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'];
    $tipo = $_POST['rol'];

    if (empty($nombre)) {
        $message = "El campo de nombre es obligatorio.";
    } elseif (empty($email)) {
        $message = "El campo de correo electrónico es obligatorio.";
    } elseif (empty($usuario)) {
        $message = "El campo de nombre de usuario es obligatorio.";
    } else {
        // Verificar si el correo ya existe en la base de datos
        $sql_check = "SELECT COUNT(*) FROM Usuario WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = "El correo electrónico ya está registrado.";
        } else {
            $hashed_password = $password; // Usar la contraseña directamente

            // Insertar el nuevo usuario en la base de datos
            $sql = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Error en la consulta SQL: " . $conn->error);
            }

            $stmt->bind_param("ssssss", $rut, $nombre, $email, $usuario, $hashed_password, $tipo);

            if ($stmt->execute()) {
                $message = "Usuario registrado exitosamente.";
                $showLoginButton = true;
            } else {
                $message = "Error al registrar usuario: " . $stmt->error;
                $showLoginButton = false;
            }

            $stmt->close();
        }
    }
}

$conn->close(); // Asegúrate de que $conn esté definido correctamente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../css/register.css"> <!-- Archivo CSS externo -->
</head>
<body>
    <div class="form-container">
        <h2>Formulario de Registro</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="../php/register.php" method="post">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required>

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            
            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="rol">Tipo:</label>
            <select id="rol" name="rol" required>
                <option value="Autor">Autor</option>
                <option value="Revisor">Revisor</option>
            </select>

            <input type="submit" value="Registrar" class="btn">
        </form>
        <a href="../php/login.php" class="btn-secondary">Ir al Login</a>
        <a href="dashboard.php" class="btn-secondary">Volver al inicio</a>
    </div>
</body>
</html>