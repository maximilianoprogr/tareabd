<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // Inserta el nuevo usuario en la base de datos
    $sql = "INSERT INTO Usuario (userid, password) VALUES ('$userid', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado exitosamente";
    } else {
        echo "Error al registrar usuario: " . $conn->error;
    }
}

$conn->close();
?>
