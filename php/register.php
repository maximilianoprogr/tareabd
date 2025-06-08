<?php
// Configurar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión a la base de datos
include('../php/db_connection.php');

$message = ""; // Inicializar mensaje vacío para mostrar errores o confirmaciones
$showLoginButton = false; // Variable para controlar la visualización del botón de login

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut']; // Obtener el RUT del formulario
    $nombre = $_POST['nombre'] ?? null; // Obtener el nombre del formulario
    $usuario = $_POST['usuario'] ?? null; // Obtener el nombre de usuario del formulario
    $email = $_POST['email'] ?? null; // Obtener el email del formulario
    $password = $_POST['password']; // Obtener la contraseña del formulario
    $tipo = $_POST['rol']; // Obtener el rol del formulario

    // Validar que el campo de nombre no esté vacío
    if (empty($nombre)) {
        $message = "El campo de nombre es obligatorio.";
    } elseif (empty($email)) {
        $message = "El campo de correo electrónico es obligatorio.";
    } elseif (empty($usuario)) {
        $message = "El campo de nombre de usuario es obligatorio.";
    } else {
        
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
            $hashed_password = $password; 

            $sql = "INSERT INTO Usuario (rut, nombre, email, usuario, password, tipo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Error en la consulta SQL: " . $conn->error);
            }

            $stmt->bind_param("ssssss", $rut, $nombre, $email, $usuario, $hashed_password, $tipo);

            if ($stmt->execute()) {
                // Si el usuario es Revisor, agregarlo también a la tabla Revisor
                if ($tipo === 'Revisor') {
                    $sql_revisor = "INSERT INTO Revisor (rut) VALUES (?)";
                    $stmt_revisor = $conn->prepare($sql_revisor);
                    $stmt_revisor->bind_param("s", $rut);
                    $stmt_revisor->execute();
                    $stmt_revisor->close();
                }
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

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../css/register.css"> 
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