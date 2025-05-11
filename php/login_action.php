<?php
include('php/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    // Usar sentencia preparada para evitar inyecciones SQL
    $sql = "SELECT * FROM Usuario WHERE userid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userid);  // "s" indica que es un string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el usuario
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verificar si la contraseña coincide (suponiendo que la contraseña se guarda cifrada)
        if (password_verify($password, $user['password'])) {
            // Si la contraseña es correcta, se redirige al usuario a la página principal
            header("Location: php/index.php");
            exit();
        } else {
            echo "Contraseña incorrecta";
        }
    } else {
        echo "Usuario no encontrado";
    }
    $stmt->close();
}

$conn->close();
?>