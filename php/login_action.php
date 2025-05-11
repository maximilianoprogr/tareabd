<?php
include('../php/conexion.php');

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($rut) || empty($password)) {
        echo "Por favor, complete todos los campos.";
        exit();
    }

    // Usar sentencia preparada para evitar inyecciones SQL
    $sql = "SELECT * FROM Usuario WHERE rut = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rut);  // "s" indica que es un string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el usuario
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Mensaje de depuración para verificar valores exactos
        echo "<br>Contraseña ingresada (depuración): '" . addslashes($password) . "'";
        echo "<br>Contraseña almacenada (depuración): '" . addslashes($user['password']) . "'";

        // Eliminar cualquier lógica relacionada con cifrado o descifrado de contraseñas
        // Comparar directamente las contraseñas ingresadas y almacenadas
        if ($password === $user['password']) {
            echo "Contraseña verificada correctamente.<br>";
            // Si la contraseña es correcta, se redirige al usuario a la página principal
            session_start();
            $_SESSION['rut'] = $user['rut'];
            $_SESSION['nombre'] = $user['nombre'];
            header("Location: ../php/index.php");
            exit();
        } else {
            echo "Contraseña incorrecta.<br>";
        }
    } else {
        echo "Usuario no encontrado.<br>";
    }
    $stmt->close();
}

$conn->close();
?>