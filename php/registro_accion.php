<?php
include('../php/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = trim($_POST['rut']);
    $password = trim($_POST['password']);

    // Validaciones de entrada
    if (empty($rut) || empty($password)) {
        echo "El RUT y la contraseña son obligatorios.";
        exit();
    }

    if (!preg_match('/^[0-9]+[kK0-9]$/', $rut)) {
        echo "El RUT no tiene un formato válido.";
        exit();
    }

    // Usar sentencia preparada para evitar inyecciones SQL
    $sql = "INSERT INTO Usuario (rut, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $rut, $password);

        if ($stmt->execute()) {
            echo "Usuario registrado exitosamente";
        } else {
            echo "Error al registrar usuario: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

$conn->close();
?>
