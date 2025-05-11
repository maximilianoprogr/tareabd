<?php
include('php/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $password = $_POST['password'];

    // Inserta el nuevo usuario en la base de datos
    $sql = "INSERT INTO Usuario (rut, password) VALUES ('$rut', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado exitosamente";
    } else {
        echo "Error al registrar usuario: " . $conn->error;
    }
}

$conn->close();
?>
