<?php
include('php/db_connection.php'); // Asegúrate de que este archivo define correctamente la variable $conn

$message = ""; // Variable para almacenar mensajes
$showLoginButton = false; // Variable para controlar la visibilidad del botón de login

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $password = $_POST['password'];
    $tipo = $_POST['rol'];
    $email = $_POST['email'] ?? null;
    $nombre = $_POST['nombre'] ?? null;

    if (empty($nombre)) {
        $message = "El campo de nombre es obligatorio.";
    } elseif (empty($email)) {
        $message = "El campo de correo electrónico es obligatorio.";
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
            // Cifrar la contraseña antes de almacenarla
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario en la base de datos con la contraseña cifrada y el tipo
            $sql = "INSERT INTO Usuario (rut, nombre, email, password, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Verificar si la consulta fue preparada correctamente
            if (!$stmt) {
                die("Error en la consulta SQL: " . $conn->error);
            }

            $stmt->bind_param("sssss", $rut, $nombre, $email, $hashed_password, $tipo);

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
    <title>Registro</title>
    <style>
        .btn-primary {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Formulario de Registro</h2>
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" required><br><br>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required><br><br>
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required><br><br>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required><br><br>
            <label for="rol">Tipo:</label>
            <select id="rol" name="rol" required>
                <option value="Autor">Autor</option>
                <option value="Revisor">Revisor</option>
            </select><br><br>
            <input type="submit" value="Registrar">
        </form>
        <a href="login.php" class="btn-primary">Ir al Login</a>
    </div>
</body>
</html>
