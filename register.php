<?php
include('db_connection.php'); // Asegúrate de que este archivo define correctamente la variable $conn

$message = ""; // Variable para almacenar mensajes

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_POST['userid'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    // Cifrar la contraseña antes de almacenarla
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario en la base de datos con la contraseña cifrada y el rol
    $sql = "INSERT INTO Usuario (userid, password, rol) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Verificar si la consulta fue preparada correctamente
    if (!$stmt) {
        die("Error en la consulta SQL: " . $conn->error);
    }

    $stmt->bind_param("sss", $userid, $hashed_password, $rol);

    if ($stmt->execute()) {
        $message = "Usuario registrado exitosamente.";
    } else {
        $message = "Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close(); // Asegúrate de que $conn esté definido correctamente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h2>Formulario de Registro</h2>
    <?php if (!empty($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="register.php" method="post">
        <label for="userid">Usuario:</label>
        <input type="text" id="userid" name="userid" required><br><br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="autor">Autor</option>
            <option value="revisor">Revisor</option>
        </select><br><br>
        <input type="submit" value="Registrar">
    </form>
</body>
</html>
