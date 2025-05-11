<?php
include('../php/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // Cifrar la contraseña antes de guardarla
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario en la base de datos con la contraseña cifrada
    $sql = "INSERT INTO Usuario (userid, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $userid, $hashed_password);

    if ($stmt->execute()) {
        echo "Usuario registrado exitosamente";
    } else {
        echo "Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
