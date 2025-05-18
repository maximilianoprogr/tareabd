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

        // Depurar el valor de tipo_usuario para verificar el rol
        if (isset($user['tipo_usuario'])) {
            echo "<p>Rol detectado: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p>Error: No se detectó el rol del usuario.</p>";
        }

        // Mensaje de depuración para verificar el rol recuperado de la base de datos
        if (isset($user['tipo_usuario'])) {
            echo "<p style='color: green;'>Rol recuperado de la base de datos: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p style='color: red;'>Error: No se encontró el rol en la base de datos.</p>";
        }

        // Eliminar cualquier lógica relacionada con cifrado o descifrado de contraseñas
        // Comparar directamente las contraseñas ingresadas y almacenadas
        if ($password === $user['password']) {
            echo "Contraseña verificada correctamente.<br>";
            // Si la contraseña es correcta, se redirige al usuario a la página principal
            
            // Reiniciar la sesión completamente antes de asignar un nuevo rol
            session_start();
            session_unset();
            session_destroy();
            session_start();

            // Mensaje de depuración para verificar el rol recuperado de la base de datos
            if (isset($user['tipo_usuario'])) {
                echo "<p style='color: green;'>Rol recuperado de la base de datos: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
            } else {
                echo "<p style='color: red;'>Error: No se encontró el rol en la base de datos.</p>";
            }

            // Asignar valores a la sesión
            $_SESSION['rut'] = $user['rut'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['tipo_usuario'];

            // Mensaje de depuración para verificar el rol asignado a la sesión
            echo "<p style='color: blue;'>Rol asignado a la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";

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