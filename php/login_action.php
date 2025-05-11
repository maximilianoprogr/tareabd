<?php
include('../php/conexion.php');

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
        
        // Verificar si la contraseña coincide (suponiendo que la contraseña se guarda cifrada)
        if (password_verify($password, $user['password'])) {
            // Si la contraseña es correcta, se redirige al usuario a la página principal
            if ($user['rut'] === '11' && $password === '11') {
                session_start();
                $_SESSION['usuario'] = 'admin';
                header("Location: ../php/dashboard.php");
                exit();
            }
            session_start();
            $_SESSION['rut'] = $user['rut'];
            $_SESSION['nombre'] = $user['nombre'];
            header("Location: ../php/index.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
    $stmt->close();
}

$conn->close();
?>