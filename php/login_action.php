<?php
include('../php/conexion.php');

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

    $sql = "SELECT * FROM Usuario WHERE rut = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rut);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        echo "<br>Contraseña ingresada (depuración): '" . addslashes($password) . "'";
        echo "<br>Contraseña almacenada (depuración): '" . addslashes($user['password']) . "'";

        if (isset($user['tipo_usuario'])) {
            echo "<p>Rol detectado: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p>Error: No se detectó el rol del usuario.</p>";
        }

        if (isset($user['tipo_usuario'])) {
            echo "<p style='color: green;'>Rol recuperado de la base de datos: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
        } else {
            echo "<p style='color: red;'>Error: No se encontró el rol en la base de datos.</p>";
        }

       
        if ($password === $user['password']) {
            echo "Contraseña verificada correctamente.<br>";
            
            session_start();
            session_unset();
            session_destroy();
            session_start();

            if (isset($user['tipo_usuario'])) {
                echo "<p style='color: green;'>Rol recuperado de la base de datos: " . htmlspecialchars($user['tipo_usuario']) . "</p>";
            } else {
                echo "<p style='color: red;'>Error: No se encontró el rol en la base de datos.</p>";
            }

            $_SESSION['rut'] = $user['rut'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['tipo_usuario'];

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