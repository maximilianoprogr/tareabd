<?php
include('../php/conexion.php');

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si la solicitud es de tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos enviados desde el formulario
    $rut = $_POST['rut'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar que ambos campos estén completos
    if (empty($rut) || empty($password)) {
        echo "Por favor, complete todos los campos.";
        exit();
    }

    // Preparar la consulta SQL para buscar al usuario por su RUT
    $sql = "SELECT * FROM Usuario WHERE rut = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rut);  // Vincular el parámetro RUT a la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró un usuario con el RUT proporcionado
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Mostrar contraseñas para depuración (no recomendado en producción)
        echo "<br>Contraseña ingresada (depuración): '" . addslashes($password) . "'";
        echo "<br>Contraseña almacenada (depuración): '" . addslashes($user['password']) . "'";

        // Verificar si el usuario tiene un rol asignado
        if (isset($user['tipo_usuario'])) {
            echo "<p>Rol detectado: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p>Error: No se detectó el rol del usuario.</p>";
        }

        // Verificar nuevamente el rol del usuario para mostrar mensajes adicionales
        if (isset($user['tipo_usuario'])) {
            echo "<p style='color: green;'>Rol recuperado de la base de datos: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p style='color: red;'>Error: No se encontró el rol en la base de datos.</p>";
        }

        // Comparar la contraseña ingresada con la almacenada en la base de datos
        if ($password === $user['password']) {
            echo "Contraseña verificada correctamente.<br>";

            // Iniciar una nueva sesión
            session_start();
            session_unset();
            session_destroy();
            session_start();

            // Almacenar datos del usuario en la sesión
            $_SESSION['rut'] = $user['rut'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['tipo_usuario'];

            echo "<p style='color: blue;'>Rol asignado a la sesión: " . htmlspecialchars($_SESSION['rol']) . "</p>";

            // Redirigir al usuario a la página principal
            header("Location: ../php/index.php");
            exit();
        } else {
            // Mensaje de error si la contraseña es incorrecta
            echo "Contraseña incorrecta.<br>";
        }
    } else {
        // Mensaje de error si no se encuentra el usuario
        echo "Usuario no encontrado.<br>";
    }
    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>