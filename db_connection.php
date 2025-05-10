<?php
$servername = "localhost"; // Puede ser localhost o la IP de tu servidor
$username = "root"; // Por defecto, el usuario de MySQL en XAMPP es 'root'
$password = ""; // Dejar vacío si no tienes contraseña para el usuario 'root'
$dbname = "base11"; // El nombre de la base de datos que estás usando

// Crea la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica si hay errores en la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
